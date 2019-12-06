<?php
/**
 * Cronjob for Tracking
 *
 * Used to update package tracking information from API
 *
 * @package    Eshoper/LPExpress/Block/Adminhtml/Form/Field
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Cron;

class Tracking
{
    /**
     * @var \Eshoper\LPExpress\Helper\ApiHelper
     */
    protected $_apiHelper;

    /**
     * @var \Eshoper\LPExpress\Model\TrackingFactory
     */
    protected $_trackingFactory;

    /**
     * Tracking constructor.
     * @param \Eshoper\LPExpress\Helper\ApiHelper $apiHelper
     */
    public function __construct (
        \Eshoper\LPExpress\Helper\ApiHelper $apiHelper,
        \Eshoper\LPExpress\Model\TrackingFactory $trackingFactory
    ) {
        $this->_apiHelper = $apiHelper;
        $this->_trackingFactory = $trackingFactory;
    }

    /**
     * Update tracking events with cron
     */
    public function execute()
    {
        $trackingData = $this->_apiHelper->getTrackingEvents ();

        if ( $trackingData && property_exists ( $trackingData, 'data' ) ) {
            // Truncate event list
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
            $resource = $objectManager->get ( 'Magento\Framework\App\ResourceConnection' );
            $connection = $resource->getConnection ();
            $tableName = $resource->getTableName ( 'lpexpress_tracking_events' );
            $connection->query ( 'TRUNCATE ' . $tableName );

            foreach ( $trackingData->data as $item ) {
                foreach ( $item as $object ) {
                    $identcode = ( string )$object->identcode;
                    foreach ( $object->events->event as $event ) {
                        $title = ( string )$event->title;
                        $time = ( string )$event->time;

                        $this->_trackingFactory->create ()
                            ->setIdentcode ( $identcode )
                            ->setEvent ( $title )
                            ->setTime ( $time )
                            ->save ();
                    }
                }
            }
        }
    }
}
