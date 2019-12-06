define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Ui/js/lib/validation/utils'
], function ($, quote, utils) {
    "use strict";
    return function ( validator ) {
        // validator.addRule ( 'select-terminal-required', function () {
        //     var method = quote.shippingMethod();
        //     var validation = method.method_code === 'lpexpress_terminal' && utils.isEmpty($( '.select-terminal-required' ).val ());
        //     return !validation;
        // }, $.mage.__( 'Please select LP Express terminal.' ) );

        return validator;
    }
});
