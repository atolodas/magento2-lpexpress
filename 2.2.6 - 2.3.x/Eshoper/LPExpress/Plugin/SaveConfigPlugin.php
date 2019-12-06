<?php
/**
 * Save configuration plugin
 *
 * On save shipping settings download terminal list
 * and overseas destinations
 *
 * @package    Eshoper/LPExpress/Plugin
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Plugin;

use Eshoper\LPExpress\Model\OverseasDestination;
use Eshoper\LPExpress\Model\OverseasRates;
use Eshoper\LPExpress\Model\ResrouceModel\OverseasRates\Collection;

class SaveConfigPlugin
{
    /**
     * API Helper
     *
     * @var \Eshoper\LPExpress\Helper\ApiHelper $_apiHelper
     */
    protected $_apiHelper;

    /**
     * Terminal model
     *
     * @var \Eshoper\LPExpress\Model\TerminalFactory $_terminalFactory
     */
    protected $_terminalFactory;

    /**
     * Module configuration
     *
     * @var \Eshoper\LPExpress\Model\Config $_config
     */
    protected $_config;


    /**
     * Add error messages to the admin
     *
     * @var \Magento\Framework\Message\ManagerInterface $_messageManager
     */
    protected $_messageManager;

    /**
     * Overseas destination model
     *
     * @var \Eshoper\LPExpress\Model\OverseasDestinationFactory
     */
    protected $_overseasDestinationFactory;

    /**
     * Overseas rates model
     *
     * @var \Eshoper\LPExpress\Model\OverseasRatesFactory
     */
    protected $_overseasRatesFactory;

    /**
     * SaveConfigPlugin constructor.
     * @param \Eshoper\LPExpress\Helper\ApiHelper $apiHelper
     * @param \Eshoper\LPExpress\Model\TerminalFactory $terminalFactory
     * @param \Eshoper\LPExpress\Model\OverseasDestinationFactory $overseasDestinationFactory
     * @param \Eshoper\LPExpress\Model\OverseasRatesFactory $overseasRatesFactory
     * @param \Eshoper\LPExpress\Model\Config $config
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct (
        \Eshoper\LPExpress\Helper\ApiHelper $apiHelper,
        \Eshoper\LPExpress\Model\TerminalFactory $terminalFactory,
        \Eshoper\LPExpress\Model\OverseasDestinationFactory $overseasDestinationFactory,
        \Eshoper\LPExpress\Model\OverseasRatesFactory $overseasRatesFactory,
        \Eshoper\LPExpress\Model\Config $config,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_apiHelper = $apiHelper;
        $this->_terminalFactory = $terminalFactory;
        $this->_config = $config;
        $this->_overseasDestinationFactory = $overseasDestinationFactory;
        $this->_overseasRatesFactory = $overseasRatesFactory;
        $this->_messageManager = $messageManager;
    }

    /**
     * Save terminal list and overseas destinations
     * to table after option save
     *
     * @param \Magento\Config\Model\Config $subject
     * @param \Magento\Config\Model\Config\Interceptor $interceptor
     * @return \Magento\Config\Model\Config\Interceptor|mixed|null
     * @throws \Exception
     */
    public function afterSave (
        \Magento\Config\Model\Config $subject,
        \Magento\Config\Model\Config\Interceptor $interceptor
    ) {
        if ( $subject->getSection() === 'carriers' && $this->_config->isEnabled () ) {

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
            $resource = $objectManager->get ( 'Magento\Framework\App\ResourceConnection' );
            $connection = $resource->getConnection ();

            // Terminals
            if ( !empty ( $terminalList = $this->_apiHelper->getTerminalList () ) ) {
                // Truncate terminal list
                $tableName = $resource->getTableName ( 'lpexpress_terminal_list' );
                $connection->query ( 'TRUNCATE ' . $tableName );

                // Recreate terminal list
                foreach ( $terminalList as $machineid => $terminal ) {
                    $this->_terminalFactory->create ()
                        ->setTerminalId ( $machineid )
                        ->setTerminal ( $terminal )
                        ->save ();
                }
            } else {
                $this->_messageManager->addErrorMessage ( __( 'Failed to get LP Express terminal list. 
                    Please check your credentials and try again.' ) );
            }
            // Overseas destinations
            if ( !empty ( $overseasDestList = $this->_apiHelper->getOverseasDestinations() ) ) {
                // Truncate overseas destination list
                $tableName = $resource->getTableName ( 'lpexpress_overseas_destinations' );
                $connection->query ( 'TRUNCATE ' . $tableName );

                $oversesRatesCollection = $this->_overseasRatesFactory->create ()->getCollection ();

                // Recreate overseas destination list
                foreach ( $overseasDestList as $destination ) {
                    $overseasRate = $oversesRatesCollection
                        ->addFieldToFilter ( 'country_id', $destination->code );

                    // Add destination codes to rates table for export csv
                    if ( empty ( $overseasRate->getData () ) ) {
                        $this->_overseasRatesFactory->create ()
                            ->setCountryId ( $destination->code )
                            ->save ();
                    }

                    $this->_overseasDestinationFactory->create ()
                        ->setCountryId ( $destination->code )
                        ->setCountryLabel ( $destination->nameen )
                        ->setTerminal ( $destination->terminal )
                        ->save ();
                }
            }
        }

        return $interceptor;
    }
}
