<?php
/**
 * Customs value used in packaging for COD functionality
 *
 * When creating packages you need to write COD value
 * for shipment buyout from courier. This field represents that.
 *
 * @package    Eshoper/LPExpress/Block/Adminhtml/Order
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Block\Adminhtml\Order;

class Packaging extends \Magento\Shipping\Block\Adminhtml\Order\Packaging
{
    /**
     * Condition to display customs value used for COD
     *
     * @return bool
     */
    public function displayCustomsValue ()
    {
//      $payment = $this->getShipment ()->getOrder ()->getPayment ()->getMethod ();
//      return $payment === 'cashondelivery' || parent::displayCustomsValue ();
        return parent::displayCustomsValue ();
    }
}
