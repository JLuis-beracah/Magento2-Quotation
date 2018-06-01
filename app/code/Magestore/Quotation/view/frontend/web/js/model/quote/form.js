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

    var quoteRequest = new CartQuote();
    window.quoteRequest = quoteRequest;
});
