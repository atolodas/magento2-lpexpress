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


use Magento\Sales\Model\Order\ShipmentRepository;
use Magento\Sales\Model\ResourceModel\Order;

class MassManifest extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $_filter;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentRepository
     */
    protected $_shipmentRepository;

    /**
     * @var \Eshoper\LPExpress\Helper\ApiHelper
     */
    protected $_apiHelper;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList;

    /**
     * MassManifest constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param Order\CollectionFactory $orderCollectionFactory
     * @param ShipmentRepository $shipmentRepository
     * @param \Eshoper\LPExpress\Helper\ApiHelper $apiHelper
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     */
    public function __construct (
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \Eshoper\LPExpress\Helper\ApiHelper $apiHelper,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        $this->_filter                      = $filter;
        $this->_collectionFactory           = $orderCollectionFactory;
        $this->_shipmentRepository          = $shipmentRepository;
        $this->_apiHelper                   = $apiHelper;
        $this->_fileFactory                 = $fileFactory;
        $this->_filesystem                  = $filesystem;
        $this->_directoryList               = $directoryList;
        parent::__construct ( $context );
    }

    /**
     * Download and write pdf document from url
     *
     * @param $url
     * @return mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function downloadPfdDocument ( $url )
    {
        $tmpManifestFile   = 'tmp/lpexpress/manifest_' . uniqid() . '.pdf';
        $media             = $this->_filesystem->getDirectoryWrite ( $this->_directoryList::MEDIA );

        $media->writeFile( $tmpManifestFile, @file_get_contents ( $url ) );

        return $this->_directoryList::MEDIA . DIRECTORY_SEPARATOR . $tmpManifestFile;
    }

    /**
     * Merge manifest documents into one and send it to the browser
     *
     * @param array $manifestUrls
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Zend_Pdf_Exception
     */
    protected function getMergedPdfDocs ( $manifestUrls = [] )
    {
        $docs           = [];
        $mergedDocs     = new \Zend_Pdf ();
        $mergedDocDir   = $this->_directoryList::MEDIA . DIRECTORY_SEPARATOR . 'tmp/lpexpress/manifest_merged.pdf';

        // Download pdf documents
        foreach ( $manifestUrls as $manifestUrl ) {
            $docs [] = $this->downloadPfdDocument ( $manifestUrl );
        }

        // Merge the documents into new
        foreach ( $docs as $doc ) {
            $loadDoc = \Zend_Pdf::load ( $doc );
            // Add pages to the new document
            foreach ( $loadDoc->pages as $page ) {
                $clonedPage = clone $page;
                $mergedDocs->pages [] = $clonedPage;
            }
            unset ( $clonedPage );
        }

        // Save document to tmp dir
        $mergedDocs->save ( $mergedDocDir );

        // Send document to the browser
        $this->_fileFactory->create(
            'manifest_' . uniqid () . '.pdf',
            @file_get_contents ( $mergedDocDir )
        );
    }


    /**
     * Collect individual shipping methods parcels
     *
     * @param $collection
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function collectParcels ( $collection )
    {
        $parcels = [];

        foreach ( $collection as $order ) {
            // Allow generate only for lpexpress shipping method
            if ( strpos ( $order->getShippingMethod (), 'lpexpress' ) === false ) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('LP Express manifest is not available for order ' . $order->getIncrementId () ) );
            }

            if ( strpos ( $this->_redirect->getRefererUrl(), 'shipment' ) !== false ) {
                // This means it was called from shipment grid
                $orderShipment = $this->_shipmentRepository->get ( $order->getEntityId () );
                $track = $orderShipment->getOrder ()->getTracksCollection ()->getLastItem ();

                // Get order from shipment
                $order = $orderShipment->getOrder();
            } else {
                // This means it was called from order grid
                $track = $order->getTracksCollection ()->getLastItem ();
            }

            if ( $track->getTrackNumber () != null ) {
                // Push tracking number by shipping method
                $parcels [ $order->getShippingMethod () ][] = $track->getTrackNumber ();
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __( 'Please generate LP Express label first for order %1', $order->getIncrementId () ) );
            }
        }

        return $parcels;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create ( $this->resultFactory::TYPE_REDIRECT );

        try {
            $collection = $this->_filter->getCollection ( $this->_collectionFactory->create() );
            $parcels = $this->collectParcels ( $collection );

            if ( !empty ( $parcels ) ) {
                $manifestUrls = [];

                foreach ( $parcels as $method => $parcel ) {
                    $call = $this->_apiHelper->callCourier ( $parcel );
                    if ( $call->calls && $manifestid = $call->calls [ 0 ]->manifestid ) {
                        $manifestUrls [] = $this->_apiHelper->getManifestUri ( $manifestid );
                    } else {
                        $this->messageManager->addErrorMessage ( __( 'Could not create manifest.' ) );
                        return $resultRedirect->setPath ( $this->_redirect->getRefererUrl () );
                    }
                }

                if ( !empty ( $manifestUrls ) ) {
                    // Send the merged docs to the browser
                    $this->getMergedPdfDocs ( $manifestUrls );
                }
            } else {
                $this->messageManager->addErrorMessage ( __( 'No parcels were found.' ) );
                return $resultRedirect->setPath ( $this->_redirect->getRefererUrl () );
            }
        } catch ( \Exception $e ) {
            $this->messageManager->addErrorMessage ( $e->getMessage () );
            return $resultRedirect->setPath ( $this->_redirect->getRefererUrl () );
        }
    }
}
