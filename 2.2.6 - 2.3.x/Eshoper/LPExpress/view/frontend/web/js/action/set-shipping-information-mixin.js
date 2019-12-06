define([
    'jquery'
], function ($) {
    'use strict';

    $(document).ready(function () {
        $(document).on('change', "[name='region_id']", function () {
            var terminalDropdown = $ ( '.select-terminal-required' );
            var terminalOptgroups = $ (".select-terminal-required optgroup");

            var regionArray = {
                'Alytaus Apskritis': 'Alytus',
                'Kauno Apskritis': 'Kaunas',
                'Klaipėdos Apskritis': 'Klaipėda',
                'Marijampolės Apskritis': 'Marijampolė',
                'Panevėžio Apskritis': 'Panevėžys',
                'Šiaulių Apskritis': 'Šiauliai',
                'Tauragės Apskritis': 'Tauragė',
                'Telšių Apskritis': 'Telšiai',
                'Utenos Apskritis': 'Utena',
                'Vilniaus Apskritis': 'Vilnius'
            };
            var region = regionArray [ $ ( "[name='region_id'] option:selected" ).text () ];

            if ( region !== null && region !== undefined ) {
                terminalDropdown.html ( '<option value="">' + $.mage.__( 'Please select LP Express terminal..' ) + '</option>' );
                terminalDropdown.append ( terminalOptgroups.sort ( function ( a, b ) {
                    return a.label === region ? -1 : 0;
                }));
                terminalDropdown.prop ( 'selectedIndex', 0 ).change ();
            }
        });
    });

    return function ( targetModule ) {
        return targetModule;
    };
});
