<?php
/**
 * Cronjob for Terminal
 *
 * Used for terminal updates every day at 1 am
 *
 * @package    Eshoper/LPExpress/Block/Adminhtml/Form/Field
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Cron;

class Terminal
{
    /**
     * @var \Eshoper\LPExpress\Helper\ApiHelper
     */
    protected $_apiHelper;

    /**
     * @var \Eshoper\LPExpress\Model\TerminalFactory
     */
    protected $_terminalFactory;

    /**
     * @var \Eshoper\LPExpress\Model\Config
     */
    protected $_config;

    /**
     * Terminal constructor.
     * @param \Eshoper\LPExpress\Helper\ApiHelper $apiHelper
     * @param \Eshoper\LPExpress\Model\TerminalFactory $terminalFactory
     * @param \Eshoper\LPExpress\Model\Config $config
     */
    public function __construct (
        \Eshoper\LPExpress\Helper\ApiHelper $apiHelper,
        \Eshoper\LPExpress\Model\TerminalFactory $terminalFactory,
        \Eshoper\LPExpress\Model\Config $config
    ) {
        $this->_apiHelper = $apiHelper;
        $this->_terminalFactory = $terminalFactory;
        $this->_config = $config;
    }

    /**
     * Cronjob update terminal list
     *
     * @throws \SoapFault
     */
    public function execute ()
    {
        if ( ! empty ( $terminalList = $this->_apiHelper->getTerminalList () ) ) {
            // Truncate terminal list
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
            $resource = $objectManager->get ( 'Magento\Framework\App\ResourceConnection' );
            $connection = $resource->getConnection ();
            $tableName = $resource->getTableName ( 'lpexpress_terminal_list' );
            $connection->query ( 'TRUNCATE ' . $tableName );

            // Recreate terminal list
            foreach ( $terminalList as $machineid => $terminal ) {
                $this->_terminalFactory->create ()
                    ->setTerminalId ( $machineid )
                    ->setTerminal ( $terminal )
                    ->save ();
            }
        }
    }
}
