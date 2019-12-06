<?php
/**
 * Terminal select options for adminhtml_order_create
 *
 * @package    Eshoper/LPExpress/Block/Adminhtml/Order/Create
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Block\Adminhtml\Order\Create;

class Terminal extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * @var \Eshoper\LPExpress\Model\ResrouceModel\Terminal\CollectionFactory
     */
    protected $_terminalCollection;

    /**
     * Terminal constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct (
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Eshoper\LPExpress\Model\ResrouceModel\Terminal\CollectionFactory $terminalCollection,
        array $data = []
    ){
        $this->_terminalCollection = $terminalCollection;
        parent::__construct ( $context, $sessionQuote, $orderCreate, $priceCurrency, $data );
    }

    /**
     * Show only when terminal method is selected
     *
     * @return bool
     */
    public function showSelect ()
    {
        return $this->getCreateOrderModel()->getShippingAddress()->getShippingMethod()
            === 'lpexpress_lpexpress_terminal';
    }

    /**
     * Retrieve terminal list
     *
     * @return array
     */
    public function getTerminalList ()
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

        return $formattedList;
    }
}
