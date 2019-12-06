<?php
/**
 * Checkout layer processor plugin
 *
 * Used for adding terminal custom select field to
 * shipping additional information
 *
 * @package    Eshoper/LPExpress/Model/Checkout
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model\Checkout;

class LayoutProcessorPlugin
{
    /**
     * Call method after layout process
     *
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess (
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shippingAdditional']['component'] = 'uiComponent';

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shippingAdditional']['displayArea'] = 'shippingAdditional';

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shippingAdditional']['children']['lpexpress_terminal'] = [
            'component' => 'Eshoper_LPExpress/js/methods/lpexpress',
            'config' => [
                'customScope' => 'shippingAddress',
                'template' => 'ui/form/field',
                'elementTmpl' => 'Eshoper_LPExpress/lpexpress',
                'options' => [],
            ],
            'dataScope' => 'shippingAddress.lpexpress_terminal',
            'label' => '',
            'provider' => 'checkoutProvider',
            'visible' => true,
            'validation' => [ 'select-terminal-required' => true ],
            'sortOrder' => 200,
        ];

        return $jsLayout;
    }
}
