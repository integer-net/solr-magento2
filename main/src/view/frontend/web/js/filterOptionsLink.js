(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'jquery/ui'
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    'use strict';

    $.widget("solr.filterOptionsLink", {

        options: {},

        /**
         * create routine for widget
         *
         * @private
         */
        _create: function () {

            var filter = this.element;

            filter.each(function(){
                var element = $(this);
                var tickbox = element.find('.tickbox');

                element.click(function(){
                    tickbox.toggleClass('tickbox--active');
                })
            })
        }
    });

    return $.solr.filterOptionsLink;
}));
