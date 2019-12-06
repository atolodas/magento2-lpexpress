<?php
/**
 * Checkout provider
 *
 * Used to pass terminal list to frontend
 *
 * @package    Eshoper/LPExpress/Model
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model;

class CheckoutProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    /**
     * @var ResrouceModel\Terminal\CollectionFactory
     */
    protected $_terminalCollection;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_moduleReader;

    /**
     * CheckoutProvider constructor.
     * @param ResrouceModel\Terminal\CollectionFactory $terminalCollection
     */
    public function __construct(
        \Eshoper\LPExpress\Model\ResrouceModel\Terminal\CollectionFactory $terminalCollection,
        \Magento\Framework\Module\Dir\Reader $moduleReader
    ) {
        $this->_terminalCollection = $terminalCollection;
        $this->_moduleReader = $moduleReader;
    }

    /**
     * Retrieve assoc array of checkout configuration
     * Pass terminal list to the checkout frontend
     *
     * @return array
     */
    public function getConfig()
    {
        $formattedList = [];
        $items = $this->_terminalCollection->create ()->getItems ();

        foreach ( $items as $item ) {
            $terminal = explode ( ' ', $item->getTerminal () );

            // Initialize arrays
            if ( !array_key_exists( $terminal [ 0 ], $formattedList  ) )
                $formattedList [ $terminal [ 0 ] ] = [];

            // Push the formatted list
            $formattedList [ $terminal [ 0 ] ][ $item->getTerminalId () ]
                = strstr ( $item->getTerminal (), " " );
        }

        // Media dir
        $viewDir = $this->_moduleReader->getModuleDir (
            \Magento\Framework\Module\Dir::MODULE_VIEW_DIR,
            'Eshoper_LPExpress'
        );

        return [
            'terminal' => [
                'list' => $formattedList
            ],
            'mediaUrl' => $viewDir . '/frontend/web/img/'
        ];
    }
}
