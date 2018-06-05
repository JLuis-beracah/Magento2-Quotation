/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global AdminQuote */
define([
    'jquery',
    'Magestore_Quotation/js/model/quote/scripts'
], function (jQuery) {
    'use strict';

    var $el = jQuery('#edit_form'),
        config,
        baseUrl,
        quote,
        product;

    if (!$el.length || !$el.data('quote-config')) {
        return;
    }

    config = $el.data('quote-config');
    baseUrl = $el.data('load-base-url');

    quote = new AdminQuote(config);
    quote.setLoadBaseUrl(baseUrl);

    product = new AdminProduct();

    window.quote = quote;
    window.product = product;
});
