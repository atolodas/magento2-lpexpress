define([
    'jquery',
    'ko',
    'Magento_Ui/js/form/element/select',
    'Magento_Checkout/js/model/quote',
    'jquery/validate',
    'mage/translate',
    'mage/storage'
], function ( $, ko, Select, quote, storage ) {

    function printPostOffice ( zipcode ) {
        // $ ( '#lp-post-office-address' ).trigger ( 'processStart' );
        $.ajax({
            showLoader: true,
            url: '/rest/V1/lpexpress/ziptopost',
            data: { zipcode: zipcode },
            type: 'GET',
            dataType: 'json'
        }).done ( function ( response ) {
            $ ( '#lp-post-office-address b' ).html ( response );
            // $ ( '#lp-post-office-address' ).trigger ( 'processStop' );
        });
    }

    $ ( document ).ready ( function () {
        $ ( '#checkout' ).on ( 'DOMSubtreeModified', function () {
            if ( $( 'input[name="postcode"]' ).length > 0 ) {
                var timeout = null;
                var zipcode = $( 'input[name="postcode"]' );

                if ( !window.checkoutConfig.isCustomerLoggedIn ) {
                    // Get post office on start
                    printPostOffice ( zipcode.val () );

                    // Binde keyup event
                    zipcode.on ( 'keyup', function () {
                        clearTimeout ( timeout );

                        // Need to wait after stopped typing
                        timeout = setTimeout(function () {
                            printPostOffice ( zipcode.val () );
                        }, 1000);
                    });
                } else {
                    // On init
                    printPostOffice ( quote.shippingAddress ().postcode );

                    // On change shipping address
                    // select the target node
                    var target = document.querySelector ( '.shipping-address-items' );

                    if ( target ) {
                        // create an observer instance
                        var observer = new MutationObserver(function (mutations) {
                            mutations.some(function (mutation) {
                                if (mutation.target.classList.contains('selected-item')) {
                                    printPostOffice(quote.shippingAddress().postcode);
                                    return mutation;
                                }
                            });
                        });

                        // configuration of the observer:
                        var configShipping = {attributes: true, childList: true, characterData: true, subtree: true}

                        // pass in the target node, as well as the observer options
                        observer.observe(target, configShipping);
                    }
                }

                // Observer for error messages window scroll up (little fix for usability)
                var targetMsg = document.querySelector ( '#checkout > .messages' );

                if ( targetMsg ) {
                    var observerMsg = new MutationObserver(function (mutations) {
                        mutations.some(function (mutation) {
                            window.scrollTo({top: 0, behavior: 'smooth'});
                            return mutation;
                        });
                    });

                    var configMsg = {attributes: false, childList: true, characterData: true, subtree: true};
                    observerMsg.observe(targetMsg, configMsg);
                }

                $ ( this ).unbind ( 'DOMSubtreeModified' );
            }
        });
    });

    return Select.extend({
        initialize: function () {
            this._super ();
        },
        selectedMethod: function () {
            var method = quote.shippingMethod();

            // if is selected method
            if ( method ) {
                // Hide or show terminal validation error
                $( 'div[name="shippingAddress.lpexpress_terminal"] .field-error')
                    .css ( { display: method.method_code === 'lpexpress_terminal' ? 'block' : 'none' } );

                // Add padding bottom to the container
                $( 'div[name="shippingAddress.lpexpress_terminal"]' )
                    .css ( { paddingBottom: method.method_code === 'lpexpress_terminal' ? '20px' : '0px' } );

                return method.method_code;
            }

            return null;
        },
        getTerminalList: function () { // Formatted html output to the terminal select dropdown
            var terminals = window.checkoutConfig.terminal;
            var html = '<option value="">' + $.mage.__( 'Please select LP Express terminal..' ) + '</option>';

            if ( terminals !== null && terminals !== undefined ) {
                for ( var terminal in terminals.list ) {
                    if ( terminals.list.hasOwnProperty ( terminal ) ) {
                        html += '<optgroup label = "' + terminal + '">';
                            for ( var terminalID in terminals.list [ terminal ] ) {
                                if ( terminals.list [ terminal ].hasOwnProperty ( terminalID ) ) {
                                    html += '<option value="' + terminalID + '">' + terminals.list [ terminal ][ terminalID ] + '</option>';
                                }
                            }
                        html += '</optgroup>';
                    }
                }
            }

            return html;
        }
    });
});
