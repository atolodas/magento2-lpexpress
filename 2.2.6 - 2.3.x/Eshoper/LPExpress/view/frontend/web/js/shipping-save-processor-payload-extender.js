define([ 'jquery' ], function ( $ ) {
    'use strict';

    return function (payload) {

        if ( payload.addressInformation['extension_attributes'] !== undefined ) {
            payload.addressInformation['extension_attributes']['lpexpress_terminal'] = $ ( '#lpexpress-terminal-list' ).val ();
            payload.addressInformation['extension_attributes']['lpexpress_post_office'] = $ ( '#lp-post-office-address b' ).html ();
        } else {
            payload.addressInformation['extension_attributes'] = {
                lpexpress_terminal: $ ( '#lpexpress-terminal-list' ).val (),
                lpexpress_post_office: $ ( '#lp-post-office-address b' ).html ()
            }
        }

        return payload;
    };
});
