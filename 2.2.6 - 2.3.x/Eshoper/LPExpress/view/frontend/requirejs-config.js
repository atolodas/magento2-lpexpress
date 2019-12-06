var config = {
    config: {
        mixins: {
            'Magento_Ui/js/lib/validation/validator': {
                'Eshoper_LPExpress/js/validation-mixin': true
            },
            'Magento_Checkout/js/action/set-shipping-information': {
                'Eshoper_LPExpress/js/action/set-shipping-information-mixin': true
            }
        }
    },
    'map': {
        '*': {
            'Magento_Checkout/js/model/shipping-save-processor/payload-extender'
                : 'Eshoper_LPExpress/js/shipping-save-processor-payload-extender'
        }
    }
};
