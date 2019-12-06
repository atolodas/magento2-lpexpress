<?php

namespace Eshoper\LPExpress\Model\Observer;

class PaymentCodAvailable implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_quote;

    /**
     * PaymentCodAvailable constructor.
     * @param \Magento\Checkout\Model\Session $quote
     */
    public function __construct (
        \Magento\Checkout\Model\Session $quote
    ){
        $this->_quote = $quote;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute( \Magento\Framework\Event\Observer $observer)
    {
        // Disable COD for all countries except in array
        if ( $observer->getEvent ()->getMethodInstance ()->getCode () == 'cashondelivery' ) {
            $quote = $this->_quote->getQuote ();
            $shippingMethod  = $quote->getShippingAddress ()->getShippingMethod ();
            $shippingCountry = $quote->getShippingAddress ()->getCountryId ();

            if ( strpos ( $shippingMethod, 'lpexpress' ) !== false &&
                !in_array ( $shippingCountry, [ 'LT', 'LV', 'EE' ] ) ) {
                $checkResult = $observer->getEvent ()->getResult ();
                $checkResult->setIsAvailable ( false );
            }
        }
    }
}
