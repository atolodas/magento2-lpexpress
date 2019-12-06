<?php
/**
 * Add to order shipment view observer
 *
 * Used to add terminal or post office to shipment view
 *
 * @package    Eshoper/LPExpress/Model/Observer
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model\Observer;

class AddHtmlToOrderShippingViewObserver implements
    \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\View\Element\Template
     */
    protected $_block;

    /**
     * Terminal collection
     *
     * @var \Eshoper\LPExpress\Model\ResrouceModel\Terminal\Collection
     */
    protected $_terminalCollection;

    /**
     * LPExpress api helper
     *
     * @var \Eshoper\LPExpress\Helper\ApiHelper
     */
    protected $_apiHelper;

    /**
     * AddHtmlToOrderShippingViewObserver constructor.
     *
     * @param \Magento\Framework\View\Element\Template $block
     */
    public function __construct (
        \Magento\Framework\View\Element\Template $block,
        \Eshoper\LPExpress\Model\ResrouceModel\Terminal\Collection $terminalCollection,
        \Eshoper\LPExpress\Helper\ApiHelper $apiHelper
    ) {
        $this->_block = $block;
        $this->_terminalCollection = $terminalCollection;
        $this->_apiHelper = $apiHelper;
    }

    /**
     * Pass additional template to the order_view and shipment_view
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute ( \Magento\Framework\Event\Observer $observer )
    {
        if ( $observer->getElementName () === 'order_shipping_view'
            || $observer->getElementName () === 'shipment_tracking' ) {
            $orderShippingViewBlock = $observer->getLayout ()
                ->getBlock ( $observer->getElementName () );
            $order      = $orderShippingViewBlock->getOrder ();
            $shipment   = null;

            // If in shipment page
            if ( $order === null ) {
                $shipId = $orderShippingViewBlock->getRequest ()->getParam ( 'shipment_id' );
                if ( $shipId !== null ) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $shipmentCollection = $objectManager->create('Magento\Sales\Model\Order\Shipment');
                    $shipment = $shipmentCollection->load($shipId);
                    $order = $shipment->getOrder();
                }
            }

            if ( $order !== null ) {
                $block = $this->_block;

                if ( $order->getLpexpressTerminal () !== null && $order->getShippingMethod ()
                    === 'lpexpress_lpexpress_terminal' ) {

                    // Get terminal by terminal_id from terminal collection
                    $terminal = $this->_terminalCollection
                        ->getItemByColumnValue( 'terminal_id', $order->getLpexpressTerminal () );

                    if ( $terminal !== null ) {
                        $block->setMethodName(__('Terminal'));
                        $block->setMethodInfo(
                            !$terminal->isEmpty() ? $terminal->getTerminal()
                                : __('Something wen\'t wrong here..')
                        );

                        if ($shipment !== null) {
                            echo sprintf('<b>' . __('Terminal') . '</b> %s', $terminal->getTerminal());
                        }
                    }
                }

                if ( $order->getLpexpressPostOffice () !== null && $order->getShippingMethod ()
                    === 'lpexpress_lpexpress_post_office' ) {

                    $block->setMethodName ( __( 'Post Office' ) );
                    $block->setMethodInfo (
                        $order->getData ( 'lpexpress_post_office' )
                    );

                    if ( $shipment !== null ) {
                        echo sprintf ( '<b>' . __( 'Post Office' ) . '</b> %s', $order->getData ( 'lpexpress_post_office' ) );
                    }
                }

                $block->setTemplate('Eshoper_LPExpress::order_info_shipping_info.phtml');

                // Set output to order view
                $html = $observer->getTransport ()->getOutput () . $block->toHtml ();
                $observer->getTransport ()->setOutput ( $html );
            }
        }
    }
}
