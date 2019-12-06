<?php
/**
 * Save to order observer
 *
 * Used to save terminal or post office to sales_order table
 *
 * @package    Eshoper/LPExpress/Model/Observer
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model\Observer;

class SaveMethodToOrderObserver implements
    \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * SaveDeliveryDateToOrderObserver constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct (
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute ( \Magento\Framework\Event\Observer $observer )
    {
        $order = $observer->getOrder ();
        $quoteRepository = $this->_objectManager->create ( 'Magento\Quote\Model\QuoteRepository' );
        $quote = $quoteRepository->get ( $order->getQuoteId() );

        if ( $order->getShippingMethod() === 'lpexpress_lpexpress_terminal' ) {
            $order->setLpexpressTerminal ( $quote->getLpexpressTerminal() );
        } else if ( $order->getShippingMethod () === 'lpexpress_lpexpress_post_office' ) {
            $order->setLpexpressPostOffice ( $quote->getLpexpressPostOffice () );
        }

        return $this;
    }
}
