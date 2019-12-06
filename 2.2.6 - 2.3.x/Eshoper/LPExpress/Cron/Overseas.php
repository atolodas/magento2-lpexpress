<?php
/**
 * Cronjob for Overseas Destinations
 *
 * Used for destination updates every day at 1 am
 *
 * @package    Eshoper/LPExpress/Block/Adminhtml/Form/Field
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Cron;


class Overseas
{
    /**
     * @var \Eshoper\LPExpress\Helper\ApiHelper
     */
    protected $_apiHelper;

    /**
     * @var \Eshoper\LPExpress\Model\OverseasDestinationFactory
     */
    protected $_overseasDestinationFactory;

    /**
     * Overseas constructor.
     * @param \Eshoper\LPExpress\Helper\ApiHelper $apiHelper
     * @param \Eshoper\LPExpress\Model\OverseasDestinationFactory $overseasDestinationFactory
     */
    public function __construct (
        \Eshoper\LPExpress\Helper\ApiHelper $apiHelper,
        \Eshoper\LPExpress\Model\OverseasDestinationFactory $overseasDestinationFactory
    ) {
        $this->_apiHelper = $apiHelper;
        $this->_overseasDestinationFactory = $overseasDestinationFactory;
    }

    /**
     * Truncate and recreate destination list
     *
     * @throws \SoapFault
     */
    public function execute ()
    {
        if ( !empty ( $overseasDestinations = $this->_apiHelper->getOverseasDestinations () ) ) {
            // Truncate destination list
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('lpexpress_overseas_destinations');
            $connection->query('TRUNCATE ' . $tableName);

            // Recreate it
            foreach ( $overseasDestinations as $destination ) {
                $this->_overseasDestinationFactory->create ()
                    ->setCountryId ( $destination->code )
                    ->setCountryLabel ( $destination->nameen )
                    ->save ();
            }
        }
    }
}
