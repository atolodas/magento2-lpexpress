<?php
/**
 * Mass generate labels
 *
 * @package    Eshoper/LPExpress/Controller/Adminhtml/Order
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Controller\Adminhtml\Order;

class MassLabel extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var MassAction\Filter
     */
    protected $_filter;

    /**
     * @var \Magento\Sales\Model\Convert\Order
     */
    protected $_convertOrder;

    /**
     * @var \Magento\Shipping\Model\Shipping\LabelGenerator
     */
    protected $_labelGenerator;

    /**
     * @var \Magento\Sales\Api\ShipmentTrackRepositoryInterface
     */
    protected $_shipmentTrackRepository;

    /**
     * @var \Eshoper\LPExpress\Helper\ApiHelper
     */
    protected $_apiHelper;

    /**
     * MassLabel constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Model\Convert\Order $convertOrder
     * @param \Magento\Shipping\Model\Shipping\LabelGenerator $labelGenerator
     * @param \Magento\Sales\Api\ShipmentTrackRepositoryInterface $shipmentTrackRepository
     * @param \Eshoper\LPExpress\Helper\ApiHelper $apiHelper
     */
    public function __construct (
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\Convert\Order $convertOrder,
        \Magento\Shipping\Model\Shipping\LabelGenerator $labelGenerator,
        \Magento\Sales\Api\ShipmentTrackRepositoryInterface $shipmentTrackRepository,
        \Eshoper\LPExpress\Helper\ApiHelper $apiHelper
    ) {
        $this->_filter = $filter;
        $this->_collectionFactory = $orderCollectionFactory;
        $this->_convertOrder = $convertOrder;
        $this->_labelGenerator = $labelGenerator;
        $this->_shipmentTrackRepository = $shipmentTrackRepository;
        $this->_apiHelper = $apiHelper;
        parent::__construct( $context );
    }

    /**
     * Generate label
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function generateLabel (
        \Magento\Sales\Model\Order\Shipment $shipment
    ){
        if ( $shipment ) {
            $items = [];
            $weight = $price = 0;
            $request = $this->_objectManager->create ( 'Magento\Framework\App\RequestInterface' );

            // Format packages
            foreach ( $shipment->getAllItems () as $item ) {
                $items [ $item->getOrderItemId () ] = [
                    'qty' => $item->getQty (),
                    'price' => $item->getPrice (),
                    'name' => $item->getName (),
                    'weight' => $item->getWeight (),
                    'product_id' => $item->getId (),
                    'order_item_id' => $item->getOrderItemId ()
                ];

                $weight += $item->getWeight ();
                $price += $item->getPrice ();
            }

            // COD
//          if ( $shipment->getOrder ()->getPayment ()->getMethod () === 'cashondelivery' ) {
//               $price = $shipment->getOrder ()->getGrandTotal ();
//          }

            // Set packages
            $request->setParams ([
                'packages' => [
                    '1' => [
                        'params' => [
                            'container' => '',
                            'weight' => $weight,
                            'customs_value' => $price,
                            'length' => 0,
                            'width' => 0,
                            'height' => 0,
                            'weight_units' => 'KILOGRAM',
                            'dimension_units' => 'CENTIMETER',
                            'content_type' => '',
                            'content_type_other' => ''
                        ],
                        'items' => $items
                    ]
                ]
            ]);

            try {
                // Create the shipping label
                unset ( $shipment [ 'tracks' ] );
                $this->_labelGenerator->create ( $shipment, $request );
                $shipment->save ();
            } catch ( \Exception $e ) {
                $this->messageManager->addErrorMessage( $e->getMessage() );
            }
        }
    }

    /**
     * Delete tracking info
     *
     * @param \Magento\Sales\Model\Order\Shipment $orderShipment
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    private function deleteTracks ( \Magento\Sales\Model\Order\Shipment $orderShipment )
    {
        foreach ( $orderShipment->getTracks () as $track )
        {
            $this->_shipmentTrackRepository->delete ( $track );
        }
    }

    /**
     * Cancel labels
     *
     * @param \Magento\Sales\Model\Order\Shipment $orderShipment
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \SoapFault
     */
    private function cancelLabels ( \Magento\Sales\Model\Order\Shipment $orderShipment )
    {
        $labels = [];

        // Cancel labels one by one
        foreach ($orderShipment->getTracks() as $track ) {
            $labels ['label']['identcode'] = $track->getTrackNumber ();

            if ( !empty ( $labels ) ) {
                $result = $this->_apiHelper->cancelLabels ( $labels );
                if ( $result != null && $result->labels[0]->status != 'ok' ) {
                    throw new \Magento\Framework\Exception\LocalizedException (
                        __( 'LP Express thrown an error: $1', $result->labels[0]->details )
                    );
                } else if ( $result != null && $result->labels[0]->status == 'ok' ) {
                   $this->deleteTracks ( $orderShipment );
                }
            }
        }
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException|\Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            $collection = $this->_filter->getCollection ( $this->_collectionFactory->create () );

            foreach ( $collection->getItems () as $order ) {
                // Allow generate only for lpexpress shipping method
                if ( strpos ( $order->getShippingMethod (), 'lpexpress' ) === false ) {
                    throw new \Magento\Framework\Exception\LocalizedException (
                        __( 'You can\'t create LP Express shipping labels for order $1', $order->getIncrementId () )
                    );
                }

                // Create shipment if order doesn't have one
                if ( !$order->hasShipments () ) {
                    if ( !$order->canShip () ) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __( 'You can\'t create LP Express shipping labels for order $1' . $order->getIncrementId () ) );
                    }

                    // Convert order to shipment
                    $orderShipment = $this->_convertOrder->toShipment ( $order );

                    foreach ( $order->getAllItems () as $orderItem ) {
                        // Check if virtual item and item Quantity
                        if ( !$orderItem->getQtyToShip () || $orderItem->getIsVirtual () ) {
                            continue;
                        }

                        $itemQty = $orderItem->getQtyToShip ();

                        // Convert to shipment item
                        $shipmentItem = $this->_convertOrder->itemToShipmentItem ( $orderItem )->setQty ( $itemQty );
                        $orderShipment->addItem ( $shipmentItem );
                    }



                    $orderShipment->register ();
                    $orderShipment->getOrder ()->setIsInProcess ( true );

                    $orderShipment->save ();
                    $orderShipment->getOrder()->save ();

                    $this->generateLabel ( $orderShipment );
                } else {
                    // Create label if order has shipment
                    $orderShipment = $this->_convertOrder->toShipment ( $order );
                    $orderShipment->register ();

                    // Delete tracking information
                    $this->cancelLabels ( $order->getShipmentsCollection ()->getFirstItem () );
                    $this->generateLabel ( $order->getShipmentsCollection ()->getFirstItem () );
                }
            }

            $this->messageManager->addSuccessMessage( __( 'You created the shipping labels.' ) );
            $resultRedirect = $this->resultFactory->create ( $this->resultFactory::TYPE_REDIRECT );
            return $resultRedirect->setPath ( $this->_redirect->getRefererUrl() );

        } catch ( \Exception $e ) {
            $this->messageManager->addErrorMessage ( $e->getMessage() );
            $resultRedirect = $this->resultFactory->create ( $this->resultFactory::TYPE_REDIRECT );

            return $resultRedirect->setPath ( $this->_redirect->getRefererUrl() );
        }
    }
}
