<?php
/**
 * Add additional fields to order create shipping method
 *
 * @package    Eshoper/LPExpress/Plugin/Adminhtml/Order/Create
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Plugin\Adminhtml\Order\Create;


class Additional
{
    /**
     * @param \Magento\Sales\Model\AdminOrder\Create $subject
     * @param \Magento\Sales\Model\Order\Interceptor $interceptor
     * @return \Magento\Sales\Model\Order\Interceptor|mixed|null
     * @throws \Exception
     */
    public function afterCreateOrder (
        \Magento\Sales\Model\AdminOrder\Create $subject,
        \Magento\Sales\Model\Order $order
    ) {
        if ( $terminal = $subject->getData ( 'lpexpress_terminal' ) ) {
            $order->setData ( 'lpexpress_terminal', $subject->getData ( 'lpexpress_terminal' ) );
        }

        if ( $post_office = $subject->getData ( 'lpexpress_post_office' ) ) {
            $order->setData ( 'lpexpress_post_office', $subject->getData ( 'lpexpress_post_office' ) );
        }

        return $order->save ();
    }
}
