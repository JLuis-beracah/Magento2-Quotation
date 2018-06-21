/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    "jquery",
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    "mage/translate",
    "prototype",
    "Magento_Catalog/catalog/product/composite/configure",
    'Magento_Ui/js/lib/view/utils/async',
    'mage/validation'
], function(jQuery, confirm, alert, __){

    jQuery.fn.serializeObject = function()
    {
        var o = {};
        var a = this.serializeArray();
        jQuery.each(a, function() {
            if (o[this.name]) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };

    window.AdminQuote = new Class.create();

    AdminQuote.prototype = {
        initialize : function(data){
            if(!data) data = {};
            this.loadBaseUrl    = false;
            this.quoteId     = data.quote_id ? data.quote_id : false;
            this.overlayData = $H({});
            this.isOnlyVirtualProduct = false;
            this.isShippingMethodReseted = data.shipping_method_reseted ? data.shipping_method_reseted : false;
            this.customerId     = data.customer_id ? data.customer_id : false;
            this.storeId        = data.store_id ? data.store_id : false;
            this.currencySymbol = data.currency_symbol ? data.currency_symbol : '';
            this.addresses      = data.addresses ? data.addresses : $H({});
            this.shippingAsBilling = data.shippingAsBilling ? data.shippingAsBilling : false;
            this.gridProducts   = $H({});
            this.billingAddressContainer = '';
            this.shippingAddressContainer= '';
            this.productConfigureAddFields = {};
            this.productPriceBase = {};
            this.collectElementsValue = true;
            this.summarizePrice = true;
            this.quote_listing_url = data.quote_listing_url ? data.quote_listing_url : "";
            this.can_edit = data.can_edit ? data.can_edit : false;
            jQuery.async('#quote-items', (function(){
                this.dataArea = new QuoteFormArea('data', $(this.getAreaId('data')), this);
                this.itemsArea = Object.extend(new QuoteFormArea('items', $(this.getAreaId('items')), this), {
                    addControlButton: function(button){
                        var controlButtonArea = $(this.node).select('.actions')[0];
                        if (typeof controlButtonArea != 'undefined') {
                            var buttons = controlButtonArea.childElements();
                            for (var i = 0; i < buttons.length; i++) {
                                if (buttons[i].innerHTML.include(button.label)) {
                                    return ;
                                }
                            }
                            button.insertIn(controlButtonArea, 'top');
                        }
                    }
                });

                var searchButtonId = 'add_products',
                    searchButton = new ControlButton(jQuery.mage.__('Add Products'), searchButtonId),
                    searchAreaId = this.getAreaId('search');
                searchButton.onClick = function() {
                    $(searchAreaId).show();
                    var el = this;
                    window.setTimeout(function () {
                        el.remove();
                    }, 10);
                };

                if (jQuery('#' + this.getAreaId('items')).is(':visible')) {
                    this.dataArea.onLoad = this.dataArea.onLoad.wrap(function(proceed) {
                        proceed();
                        this._parent.itemsArea.setNode($(this._parent.getAreaId('items')));
                        this._parent.itemsArea.onLoad();
                    });
                    var self = this;
                    this.itemsArea.onLoad = this.itemsArea.onLoad.wrap(function(proceed) {
                        proceed();
                        if ($(searchAreaId) && !$(searchAreaId).visible() && !$(searchButtonId) && self.can_edit) {
                            this.addControlButton(searchButton);
                        }
                    });
                    this.areasLoaded();
                    this.itemsArea.onLoad();
                }
            }).bind(this));

            jQuery('#edit_form')
                .on('submitQuote', function(){
                    jQuery(this).trigger('realQuote');
                })
                .on('realQuote', this._realSubmit.bind(this));
        },

        areasLoaded: function(){
        },

        itemsLoaded: function(){
        },

        dataLoaded: function(){
            this.dataShow();
        },

        setLoadBaseUrl : function(url){
            this.loadBaseUrl = url;
        },

        setAddresses : function(addresses){
            this.addresses = addresses;
        },

        setCustomerId : function(id){
            var self = this;
            self.customerId = id;
            self.loadArea('header', true).done(function(){
                self.setCustomerAfter();
            });
        },

        setCustomerAfter : function () {
            this.customerSelectorHide();
            if (this.storeId) {
                $(this.getAreaId('data')).callback = 'dataLoaded';
                this.loadArea(['data', 'info'], true);
            }
            else {
                this.storeSelectorShow();
            }
        },

        setStoreId : function(id){
            this.storeId = id;
            this.storeSelectorHide();
            //this.loadArea(['header', 'sidebar','data'], true);
            this.dataShow();
            this.loadArea(['header', 'data', 'info'], true);
        },

        setCurrencyId : function(id){
            this.currencyId = id;
            //this.loadArea(['sidebar', 'data'], true);
            this.loadArea(['data'], true);
        },

        setCurrencySymbol : function(symbol){
            this.currencySymbol = symbol;
        },

        selectAddress : function(el, container){
            id = el.value;
            if (id.length == 0) {
                id = '0';
            }
            if(this.addresses[id]){
                this.fillAddressFields(container, this.addresses[id]);

            }
            else{
                this.fillAddressFields(container, {});
            }

            var data = this.serializeData(container);
            data[el.name] = id;
            if(this.isShippingField(container) && !this.isShippingMethodReseted){
                this.resetShippingMethod(data);
            }
            else{
                this.saveData(data);
            }
        },

        isShippingField : function(fieldId){
            if(this.shippingAsBilling){
                return fieldId.include('billing');
            }
            return fieldId.include('shipping');
        },

        isBillingField : function(fieldId){
            return fieldId.include('billing');
        },

        bindAddressFields : function(container) {
            var fields = $(container).select('input', 'select', 'textarea');
            for(var i=0;i<fields.length;i++){
                Event.observe(fields[i], 'change', this.changeAddressField.bind(this));
            }
        },

        /**
         * Triggers on each form's element changes.
         *
         * @param {Object} event
         */
        changeAddressField: function (event) {
            var field = Event.element(event),
                re = /[^\[]*\[([^\]]*)_address\]\[([^\]]*)\](\[(\d)\])?/,
                matchRes = field.name.match(re),
                type,
                name,
                data;

            if (!matchRes) {
                return;
            }

            type = matchRes[1];
            name = matchRes[2];

            if (this.isBillingField(field.id)) {
                data = this.serializeData(this.billingAddressContainer);
            } else {
                data = this.serializeData(this.shippingAddressContainer);
            }
            data = data.toObject();

            if (type === 'billing' && this.shippingAsBilling || type === 'shipping' && !this.shippingAsBilling) {
                data['reset_shipping'] = true;
            }

            data['quote[' + type + '_address][customer_address_id]'] = null;
            data['shipping_as_billing'] = jQuery('[name="shipping_same_as_billing"]').is(':checked') ? 1 : 0;

            if (name === 'customer_address_id') {
                data['quote[' + type + '_address][customer_address_id]'] =
                    $('quote-' + type + '_address_customer_address_id').value;
            }

            if (data['reset_shipping']) {
                this.resetShippingMethod(data);
            } else {
                this.saveData(data);

                if (name === 'country_id' || name === 'customer_address_id') {
                    this.loadArea(['shipping_method', 'totals', 'items'], true, data);
                }
            }
        },

        fillAddressFields : function(container, data){
            var regionIdElem = false;
            var regionIdElemValue = false;

            var fields = $(container).select('input', 'select', 'textarea');
            var re = /[^\[]*\[[^\]]*\]\[([^\]]*)\](\[(\d)\])?/;
            for(var i=0;i<fields.length;i++){
                // skip input type file @Security error code: 1000
                if (fields[i].tagName.toLowerCase() == 'input' && fields[i].type.toLowerCase() == 'file') {
                    continue;
                }
                var matchRes = fields[i].name.match(re);
                if (matchRes === null) {
                    continue;
                }
                var name = matchRes[1];
                var index = matchRes[3];

                if (index){
                    // multiply line
                    if (data[name]){
                        var values = data[name].split("\n");
                        fields[i].value = values[index] ? values[index] : '';
                    } else {
                        fields[i].value = '';
                    }
                } else if (fields[i].tagName.toLowerCase() == 'select' && fields[i].multiple) {
                    // multiselect
                    if (data[name]) {
                        values = [''];
                        if (Object.isString(data[name])) {
                            values = data[name].split(',');
                        } else if (Object.isArray(data[name])) {
                            values = data[name];
                        }
                        fields[i].setValue(values);
                    }
                } else {
                    fields[i].setValue(data[name] ? data[name] : '');
                }

                if (fields[i].changeUpdater) fields[i].changeUpdater();
                if (name == 'region' && data['region_id'] && !data['region']){
                    fields[i].value = data['region_id'];
                }
            }
        },

        disableShippingAddress : function(flag) {
            this.shippingAsBilling = flag;
            if ($('quote-shipping_address_customer_address_id')) {
                $('quote-shipping_address_customer_address_id').disabled = flag;
            }
            if ($(this.shippingAddressContainer)) {
                var dataFields = $(this.shippingAddressContainer).select('input', 'select', 'textarea');
                for (var i = 0; i < dataFields.length; i++) {
                    dataFields[i].disabled = flag;

                    if(this.isOnlyVirtualProduct) {
                        dataFields[i].setValue('');
                    }
                }
                var buttons = $(this.shippingAddressContainer).select('button');
                // Add corresponding class to buttons while disabling them
                for (i = 0; i < buttons.length; i++) {
                    buttons[i].disabled = flag;
                    if (flag) {
                        buttons[i].addClassName('disabled');
                    } else {
                        buttons[i].removeClassName('disabled');
                    }
                }
            }
        },

        setShippingAsBilling : function(flag){
            var data;
            var areasToLoad = ['shipping_address', 'totals'];
            this.disableShippingAddress(flag);
            if(flag){
                data = this.serializeData(this.billingAddressContainer);
            } else {
                data = this.serializeData(this.shippingAddressContainer);
                areasToLoad.push('shipping_method');
            }
            data = data.toObject();
            data['shipping_as_billing'] = flag ? 1 : 0;
            data['reset_shipping'] = 1;
            this.loadArea( areasToLoad, true, data);
        },
        applyCoupon : function(code){
            this.loadArea(['items', 'shipping_method', 'totals'], true, {'quote[coupon][code]':code, reset_shipping: 0});
            this.quoteItemChanged = false;
        },
        addProduct : function(id){
            this.loadArea(['items', 'info', 'totals', 'shipping_method'], true, {add_product:id, reset_shipping: true});
        },

        removeQuoteItem : function(id){
            this.loadArea(['items', 'info', 'totals', 'shipping_method'], true,
                {remove_item:id, from:'quote', reset_shipping: true});
        },

        productGridShow : function(buttonElement){
            this.productGridShowButton = buttonElement;
            Element.hide(buttonElement);
            this.showArea('search');
        },

        productGridRowInit : function(grid, row){
            var checkbox = $(row).select('.checkbox')[0];
            var inputs = $(row).select('.input-text');
            if (checkbox && inputs.length > 0) {
                checkbox.inputElements = inputs;
                for (var i = 0; i < inputs.length; i++) {
                    var input = inputs[i];
                    input.checkboxElement = checkbox;

                    var product = this.gridProducts.get(checkbox.value);
                    if (product) {
                        var defaultValue = product[input.name];
                        if (defaultValue) {
                            if (input.name == 'giftmessage') {
                                input.checked = true;
                            } else {
                                input.value = defaultValue;
                            }
                        }
                    }

                    input.disabled = !checkbox.checked || input.hasClassName('input-inactive');

                    Event.observe(input,'keyup', this.productGridRowInputChange.bind(this));
                    Event.observe(input,'change',this.productGridRowInputChange.bind(this));
                }
            }
        },

        productGridRowInputChange : function(event){
            var element = Event.element(event);
            if (element && element.checkboxElement && element.checkboxElement.checked){
                if (element.name!='giftmessage' || element.checked) {
                    this.gridProducts.get(element.checkboxElement.value)[element.name] = element.value;
                } else if (element.name=='giftmessage' && this.gridProducts.get(element.checkboxElement.value)[element.name]) {
                    delete(this.gridProducts.get(element.checkboxElement.value)[element.name]);
                }
            }
        },

        productGridRowClick : function(grid, event){
            var trElement = Event.findElement(event, 'tr');
            var qtyElement = trElement.select('input[name="qty"]')[0];
            var eventElement = Event.element(event);
            var isInputCheckbox = eventElement.tagName == 'INPUT' && eventElement.type == 'checkbox';
            var isInputQty = eventElement.tagName == 'INPUT' && eventElement.name == 'qty';
            if (trElement && !isInputQty) {
                var checkbox = Element.select(trElement, 'input[type="checkbox"]')[0];
                var confLink = Element.select(trElement, 'a')[0];
                var priceColl = Element.select(trElement, '.price')[0];
                if (checkbox) {
                    // processing non composite product
                    if (confLink.readAttribute('disabled')) {
                        var checked = isInputCheckbox ? checkbox.checked : !checkbox.checked;
                        grid.setCheckboxChecked(checkbox, checked);
                        // processing composite product
                    } else if (isInputCheckbox && !checkbox.checked) {
                        grid.setCheckboxChecked(checkbox, false);
                        // processing composite product
                    } else if (!isInputCheckbox || (isInputCheckbox && checkbox.checked)) {
                        var listType = confLink.readAttribute('list_type');
                        var productId = confLink.readAttribute('product_id');
                        if (typeof this.productPriceBase[productId] == 'undefined') {
                            var priceBase = priceColl.innerHTML.match(/.*?([\d,]+\.?\d*)/);
                            if (!priceBase) {
                                this.productPriceBase[productId] = 0;
                            } else {
                                this.productPriceBase[productId] = parseFloat(priceBase[1].replace(/,/g,''));
                            }
                        }
                        productConfigure.setConfirmCallback(listType, function() {
                            // sync qty of popup and qty of grid
                            var confirmedCurrentQty = productConfigure.getCurrentConfirmedQtyElement();
                            if (qtyElement && confirmedCurrentQty && !isNaN(confirmedCurrentQty.value)) {
                                qtyElement.value = confirmedCurrentQty.value;
                            }
                            // calc and set product price
                            var productPrice = this._calcProductPrice();
                            if (this._isSummarizePrice()) {
                                productPrice += this.productPriceBase[productId];
                            }
                            productPrice = parseFloat(Math.round(productPrice + "e+2") + "e-2");
                            priceColl.innerHTML = this.currencySymbol + productPrice.toFixed(2);
                            // and set checkbox checked
                            grid.setCheckboxChecked(checkbox, true);
                        }.bind(this));
                        productConfigure.setCancelCallback(listType, function() {
                            if (!$(productConfigure.confirmedCurrentId) || !$(productConfigure.confirmedCurrentId).innerHTML) {
                                grid.setCheckboxChecked(checkbox, false);
                            }
                        });
                        productConfigure.setShowWindowCallback(listType, function() {
                            // sync qty of grid and qty of popup
                            var formCurrentQty = productConfigure.getCurrentFormQtyElement();
                            if (formCurrentQty && qtyElement && !isNaN(qtyElement.value)) {
                                formCurrentQty.value = qtyElement.value;
                            }
                        }.bind(this));
                        productConfigure.showItemConfiguration(listType, productId);
                    }
                }
            }
        },

        /**
         * Is need to summarize price
         */
        _isSummarizePrice: function(elm) {
            if (elm && elm.hasAttribute('summarizePrice')) {
                this.summarizePrice = parseInt(elm.readAttribute('summarizePrice'));
            }
            return this.summarizePrice;
        },
        /**
         * Calc product price through its options
         */
        _calcProductPrice: function () {
            var productPrice = 0;
            var getPriceFields = function (elms) {
                var productPrice = 0;
                var getPrice = function (elm) {
                    var optQty = 1;
                    if (elm.hasAttribute('qtyId')) {
                        if (!$(elm.getAttribute('qtyId')).value) {
                            return 0;
                        } else {
                            optQty = parseFloat($(elm.getAttribute('qtyId')).value);
                        }
                    }
                    if (elm.hasAttribute('price') && !elm.disabled) {
                        return parseFloat(elm.readAttribute('price')) * optQty;
                    }
                    return 0;
                };
                for(var i = 0; i < elms.length; i++) {
                    if (elms[i].type == 'select-one' || elms[i].type == 'select-multiple') {
                        for(var ii = 0; ii < elms[i].options.length; ii++) {
                            if (elms[i].options[ii].selected) {
                                if (this._isSummarizePrice(elms[i].options[ii])) {
                                    productPrice += getPrice(elms[i].options[ii]);
                                } else {
                                    productPrice = getPrice(elms[i].options[ii]);
                                }
                            }
                        }
                    }
                    else if (((elms[i].type == 'checkbox' || elms[i].type == 'radio') && elms[i].checked)
                        || ((elms[i].type == 'file' || elms[i].type == 'text' || elms[i].type == 'textarea' || elms[i].type == 'hidden')
                            && Form.Element.getValue(elms[i]))
                    ) {
                        if (this._isSummarizePrice(elms[i])) {
                            productPrice += getPrice(elms[i]);
                        } else {
                            productPrice = getPrice(elms[i]);
                        }
                    }
                }
                return productPrice;
            }.bind(this);
            productPrice += getPriceFields($(productConfigure.confirmedCurrentId).getElementsByTagName('input'));
            productPrice += getPriceFields($(productConfigure.confirmedCurrentId).getElementsByTagName('select'));
            productPrice += getPriceFields($(productConfigure.confirmedCurrentId).getElementsByTagName('textarea'));
            return productPrice;
        },

        productGridCheckboxCheck : function(grid, element, checked){
            if (checked) {
                if(element.inputElements) {
                    this.gridProducts.set(element.value, {});
                    var product = this.gridProducts.get(element.value);
                    for (var i = 0; i < element.inputElements.length; i++) {
                        var input = element.inputElements[i];
                        if (!input.hasClassName('input-inactive')) {
                            input.disabled = false;
                            if (input.name == 'qty' && !input.value) {
                                input.value = 1;
                            }
                        }

                        if (input.checked || input.name != 'giftmessage') {
                            product[input.name] = input.value;
                        } else if (product[input.name]) {
                            delete(product[input.name]);
                        }
                    }
                }
            } else {
                if(element.inputElements){
                    for(var i = 0; i < element.inputElements.length; i++) {
                        element.inputElements[i].disabled = true;
                    }
                }
                this.gridProducts.unset(element.value);
            }
            grid.reloadParams = {'products[]':this.gridProducts.keys()};
        },

        /**
         * Submit configured products to quote
         */
        productGridAddSelected : function(){
            if(this.productGridShowButton) Element.show(this.productGridShowButton);
            var area = ['search', 'items', 'shipping_method', 'totals'];
            // prepare additional fields and filtered items of products
            var fieldsPrepare = {};
            var itemsFilter = [];
            var products = this.gridProducts.toObject();
            for (var productId in products) {
                itemsFilter.push(productId);
                var paramKey = 'item['+productId+']';
                for (var productParamKey in products[productId]) {
                    paramKey += '['+productParamKey+']';
                    fieldsPrepare[paramKey] = products[productId][productParamKey];
                }
            }
            this.productConfigureSubmit('product_to_add', area, fieldsPrepare, itemsFilter);
            productConfigure.clean('quote_items');
            this.hideArea('search');
            this.gridProducts = $H({});
        },

        selectCustomer : function(grid, event){
            var element = Event.findElement(event, 'tr');
            if (element.title){
                this.setCustomerId(element.title);
            }
        },

        customerSelectorHide : function(){
            this.hideArea('customer-selector');
        },

        customerSelectorShow : function(){
            this.showArea('customer-selector');
        },

        storeSelectorHide : function(){
            this.hideArea('store-selector');
        },

        storeSelectorShow : function(){
            this.showArea('store-selector');
        },

        dataHide : function(){
            this.hideArea('data');
            this.hideArea('info');
        },

        dataShow : function(){
            if ($('submit_quote_top_button')) {
                $('submit_quote_top_button').show();
            }
            if ($('send_quote_top_button')) {
                $('send_quote_top_button').show();
            }
            this.showArea('data');
            this.showArea('info');
        },
        /**
         * Submit batch of configured products
         *
         * @param listType
         * @param area
         * @param fieldsPrepare
         * @param itemsFilter
         */
        productConfigureSubmit : function(listType, area, fieldsPrepare, itemsFilter) {
            // prepare loading areas and build url
            area = this.prepareArea(area);
            this.loadingAreas = area;
            var url = this.loadBaseUrl + 'block/' + area + '?isAjax=true';

            // prepare additional fields
            fieldsPrepare = this.prepareParams(fieldsPrepare);
            fieldsPrepare.reset_shipping = 1;
            fieldsPrepare.json = 1;

            // create fields
            var fields = [];
            for (var name in fieldsPrepare) {
                fields.push(new Element('input', {type: 'hidden', name: name, value: fieldsPrepare[name]}));
            }
            productConfigure.addFields(fields);

            // filter items
            if (itemsFilter) {
                productConfigure.addItemsFilter(listType, itemsFilter);
            }

            // prepare and do submit
            productConfigure.addListType(listType, {urlSubmit: url});
            productConfigure.setOnLoadIFrameCallback(listType, function(response){
                this.loadAreaResponseHandler(response);
            }.bind(this));
            productConfigure.submit(listType);
            // clean
            this.productConfigureAddFields = {};
        },

        /**
         * Show configuration of quote item
         *
         * @param itemId
         */
        showQuoteItemConfiguration: function(itemId){
            var listType = 'quote_items';
            var qtyElement = $('quote-items_grid').select('input[name="item\['+itemId+'\]\[qty\]"]')[0];
            productConfigure.setConfirmCallback(listType, function() {
                // sync qty of popup and qty of grid
                var confirmedCurrentQty = productConfigure.getCurrentConfirmedQtyElement();
                if (qtyElement && confirmedCurrentQty && !isNaN(confirmedCurrentQty.value)) {
                    qtyElement.value = confirmedCurrentQty.value;
                }
                this.productConfigureAddFields['item['+itemId+'][configured]'] = 1;

            }.bind(this));
            productConfigure.setShowWindowCallback(listType, function() {
                // sync qty of grid and qty of popup
                var formCurrentQty = productConfigure.getCurrentFormQtyElement();
                if (formCurrentQty && qtyElement && !isNaN(qtyElement.value)) {
                    formCurrentQty.value = qtyElement.value;
                }
            }.bind(this));
            productConfigure.showItemConfiguration(listType, itemId);
        },

        itemsUpdate : function(){
            var self = this;
            var area = ['items'];
            // prepare additional fields
            var fieldsPrepare = {update_items: 1};
            var info = $('quote-items_grid').select('input', 'select', 'textarea');
            for(var i=0; i<info.length; i++){
                if(!info[i].disabled && (info[i].type != 'checkbox' || info[i].checked)) {
                    fieldsPrepare[info[i].name] = info[i].getValue();
                }
            }
            self.quoteItemChanged = false;
            self.loadArea(["items", "info", 'totals', 'shipping_method'], true, fieldsPrepare);
        },

        itemsOnchangeBind : function(){
            var elems = $('quote-items_grid').select('input', 'select', 'textarea');
            for(var i=0; i<elems.length; i++){
                if(!elems[i].bindOnchange){
                    elems[i].bindOnchange = true;
                    elems[i].observe('change', this.itemChange.bind(this))
                }
            }
        },

        itemChange : function(event){
            this.quoteItemChanged = true;
        },
        expirationFieldsBind : function(inputId){
            if($(inputId)){
                var self = this;
                var input = jQuery("#"+inputId);
                input.change(function(){
                    self.expirationFieldChange(input.val());
                });
            }
        },
        expirationFieldChange : function(expirationDate){
            this.saveData({
                expiration_date:expirationDate
            });
        },
        salesrepFieldsBind : function(elementId){
            if($(elementId)){
                var self = this;
                var element = jQuery("#"+elementId);
                element.change(function(){
                    self.salesrepFieldChange(element.val());
                });
            }
        },
        salesrepFieldChange : function(salesrep){
            this.saveData({
                salesrep:salesrep
            });
        },
        additionalRecipientEmailsFieldsBind : function(elementId){
            if($(elementId)){
                var self = this;
                var element = jQuery("#"+elementId);
                element.change(function(){
                    self.additionalRecipientEmailsFieldChange(element.val());
                });
            }
        },
        additionalRecipientEmailsFieldChange : function(additional_recipient_emails){
            this.saveData({
                additional_recipient_emails:additional_recipient_emails
            });
        },

        accountFieldsBind : function(container){
            if($(container)){
                var self = this;
                var fields = $(container).select('input', 'select', 'textarea');
                for(var i=0; i<fields.length; i++){
                    if(fields[i].id == 'group_id'){
                        fields[i].observe('change',
                            function () {
                                if(self.validateElement(this)){
                                    self.accountGroupChange();
                                }
                            }
                        )
                    }
                    else{
                        fields[i].observe('change',
                            function () {
                                if(self.validateElement(this)){
                                    self.accountFieldChange();
                                }
                            }
                        )
                    }
                }
            }
        },

        accountGroupChange : function(){
            this.loadArea(['data'], true, this.serializeData('customer_account_fields').toObject());
        },

        accountFieldChange : function(){
            this.loadArea(false, true, this.serializeData('customer_account_fields').toObject());
        },
        validateElement: function(el){
            return jQuery(el).valid();
        },
        commentFieldsBind : function(container){
            if($(container)){
                var fields = $(container).select('input', 'textarea');
                for(var i=0; i<fields.length; i++)
                    fields[i].observe('change', this.commentFieldChange.bind(this))
            }
        },

        commentFieldChange : function(){
            this.saveData(this.serializeData('quote-comment'));
        },

        loadArea : function(area, indicator, params){
            var deferred = new jQuery.Deferred();
            var url = this.loadBaseUrl;
            if (area) {
                area = this.prepareArea(area);
                url += 'block/' + area;
            }
            if (indicator === true) indicator = 'html-body';
            params = this.prepareParams(params);
            params.json = true;
            if (!this.loadingAreas) this.loadingAreas = [];
            if (indicator) {
                this.loadingAreas = area;
                new Ajax.Request(url, {
                    parameters:params,
                    loaderArea: indicator,
                    onSuccess: function(transport) {
                        var response = transport.responseText.evalJSON();
                        this.loadAreaResponseHandler(response);
                        deferred.resolve(response);
                    }.bind(this)
                });
            }
            else {
                new Ajax.Request(url, {
                    parameters:params,
                    loaderArea: indicator,
                    onSuccess: function(transport) {
                        deferred.resolve(transport);
                    }
                });
            }
            return deferred.promise();
        },

        loadAreaResponseHandler : function (response) {
            if (response.error) {
                alert({
                    content: response.message
                });
            }
            if (response.ajaxExpired && response.ajaxRedirect) {
                setLocation(response.ajaxRedirect);
            }
            if (!this.loadingAreas) {
                this.loadingAreas = [];
            }
            if (typeof this.loadingAreas == 'string') {
                this.loadingAreas = [this.loadingAreas];
            }
            if (this.loadingAreas.indexOf('message') == -1) {
                this.loadingAreas.push('message');
            }
            if (response.header) {
                jQuery('.page-actions-inner').attr('data-title', response.header);
            }

            for (var i = 0; i < this.loadingAreas.length; i++) {
                var id = this.loadingAreas[i];
                if ($(this.getAreaId(id))) {
                    if ('message' != id || response[id]) {
                        $(this.getAreaId(id)).update(response[id]);
                    }
                    if ($(this.getAreaId(id)).callback) {
                        this[$(this.getAreaId(id)).callback]();
                    }
                }
            }
            if(!response.message){
                $(this.getAreaId("message")).update("");
            }
        },

        prepareArea : function(area) {
            return area;
        },

        saveData : function(data){
            this.loadArea(false, false, data);
        },

        showArea : function(area){
            var id = this.getAreaId(area);
            if($(id)) {
                $(id).show();
                this.areaOverlay();
            }
        },

        hideArea : function(area){
            var id = this.getAreaId(area);
            if($(id)) {
                $(id).hide();
                this.areaOverlay();
            }
        },

        areaOverlay : function()
        {
            $H(quote.overlayData).each(function(e){
                e.value.fx();
            });
        },

        getAreaId : function(area){
            return 'quote-'+area;
        },

        prepareParams : function(params){
            if (!params) {
                params = {};
            }
            if (!params.quote_id) {
                params.quote_id = this.quoteId;
            }
            if (!params.id && this.quoteId) {
                params.id = this.quoteId;
            }
            if (!params.customer_id) {
                params.customer_id = this.customerId;
            }
            if (!params.store_id) {
                params.store_id = this.storeId;
            }
            if (!params.currency_id) {
                params.currency_id = this.currencyId;
            }
            if (!params.form_key) {
                params.form_key = FORM_KEY;
            }
            return params;
        },

        serializeData : function(container){
            var fields = $(container).select('input', 'select', 'textarea');
            var data = Form.serializeElements(fields, true);

            return $H(data);
        },

        toggleCustomPrice: function(checkbox, elemId, tierBlock) {
            if (checkbox.checked) {
                $(elemId).disabled = false;
                $(elemId).show();
                if($(tierBlock)) $(tierBlock).hide();
            }
            else {
                $(elemId).disabled = true;
                $(elemId).hide();
                if($(tierBlock)) $(tierBlock).show();
            }
        },

        toggleAdminDiscount: function(checkbox, elemId) {
            if (checkbox.checked) {
                $(elemId).disabled = false;
                $(elemId).show();
            }
            else {
                $(elemId).disabled = true;
                $(elemId).hide();
            }
        },

        submit : function()
        {
            jQuery('#edit_form').trigger('processStart');
            jQuery('#edit_form').trigger('submitQuote');
        },

        _realSubmit: function () {
            var disableAndSave = function() {
                disableElements('save');
                jQuery('#edit_form').on('invalid-form.validate', function() {
                    enableElements('save');
                    jQuery('#edit_form').trigger('processStop');
                    jQuery('#edit_form').off('invalid-form.validate');
                });
                jQuery('#edit_form').triggerHandler('save');
            }
            if (this.quoteItemChanged) {
                var self = this;

                jQuery('#edit_form').trigger('processStop');

                confirm({
                    content: jQuery.mage.__('You have item changes'),
                    actions: {
                        confirm: function() {
                            jQuery('#edit_form').trigger('processStart');
                            disableAndSave();
                        },
                        cancel: function() {
                            self.itemsUpdate();
                        }
                    }
                });
            } else {
                disableAndSave();
            }
        },

        overlay : function(elId, show, observe) {
            if (typeof(show) == 'undefined') { show = true; }

            var quoteObj = this;
            var obj = this.overlayData.get(elId);
            if (!obj) {
                obj = {
                    show: show,
                    el: elId,
                    quote: quoteObj,
                    fx: function(event) {
                        this.quote.processOverlay(this.el, this.show);
                    }
                };
                obj.bfx = obj.fx.bindAsEventListener(obj);
                this.overlayData.set(elId, obj);
            } else {
                obj.show = show;
                Event.stopObserving(window, 'resize', obj.bfx);
            }

            Event.observe(window, 'resize', obj.bfx);

            this.processOverlay(elId, show);
        },

        processOverlay : function(elId, show) {
            var el = $(elId);

            if (!el) {
                return;
            }

            var parentEl = el.up(1);
            if (show) {
                parentEl.removeClassName('ignore-validate');
            } else {
                parentEl.addClassName('ignore-validate');
            }

            if (Prototype.Browser.IE) {
                parentEl.select('select').each(function (elem) {
                    if (show) {
                        elem.needShowOnSuccess = false;
                        elem.style.visibility = '';
                    } else {
                        elem.style.visibility = 'hidden';
                        elem.needShowOnSuccess = true;
                    }
                });
            }

            parentEl.setStyle({position: 'relative'});
            el.setStyle({
                display: show ? 'none' : ''
            });
        },
        _realSend: function(message, clearSession){
            var self = this;
            confirm({
                content: message,
                actions: {
                    confirm: function() {
                        var params = {quote_request_action: 'send'};
                        if(clearSession){
                            params.clear_session = true;
                        }
                        self.loadArea(["items", "info", 'totals', 'shipping_method'], true, params).done(function(response){
                            disableElements('save_as_draft');
                            // disableElements('decline');
                            if(clearSession){
                                if(response){
                                    if(!response.message){
                                        window.location.href = self.quote_listing_url;
                                    }
                                }
                            }
                        });
                    },
                    cancel: function() {

                    }
                }
            });
        },
        send: function(message, clearSession){
            var self = this;
            if (this.quoteItemChanged) {
                var self = this;
                confirm({
                    content: jQuery.mage.__('You have item changes'),
                    actions: {
                        confirm: function() {
                            self._realSend(message, clearSession);
                        },
                        cancel: function() {
                            self.itemsUpdate();
                        }
                    }
                });
            } else {
                self._realSend(message, clearSession);
            }
        },

        decline: function(message){
            var self = this;
            confirm({
                content: message,
                actions: {
                    confirm: function() {
                        var params = {quote_request_action: 'decline'};
                        self.loadArea(["items", "info", 'totals','shipping_method'], true, params).done(function(){
                            disableElements('save_as_draft');
                            disableElements('decline');
                            disableElements('send');
                        });
                    },
                    cancel: function() {

                    }
                }
            });
        },
        cancel: function(){
            var self = this;
            var params = {clear_session: true};
            self.loadArea([], true, params).done(function(){
                window.location.href = self.quote_listing_url;
            });
        },
        submitRequest: function(){
            var self = this;
            var params = {quote_request_action:'submit', clear_session: true};
            self.loadArea([], true, params).done(function(response){
                if(response){
                    if(!response.message){
                        window.location.href = self.quote_listing_url;
                    }
                }
            });
        },

        resetShippingMethod : function(data){
            var areasToLoad = ['totals', 'items'];
            if(!this.isOnlyVirtualProduct) {
                areasToLoad.push('shipping_method');
            }

            data['reset_shipping'] = 1;
            this.isShippingMethodReseted = true;
            this.loadArea(areasToLoad, true, data);
        },

        loadShippingRates : function(){
            this.isShippingMethodReseted = false;
            this.loadArea(['shipping_method', 'totals'], true, {collect_shipping_rates: 1});
        },

        setShippingMethod : function(method){
            var data = {};
            data['quote[shipping_method]'] = method;
            if(method == "admin_shipping_standard"){
                data['quote[admin_shipping_amount]'] = ($("admin_shipping_custom_price").value != null)?$("admin_shipping_custom_price").value:null;
                data['quote[admin_shipping_description]'] = $("admin_shipping_custom_description").value;
            }
            this.loadArea(['shipping_method', 'totals'], true, data);
        },
    };

    window.AdminProduct = new Class.create();

    AdminProduct.prototype = {
        htmlFormContainerId: "quotation_product_create_form_html_container",
        htmlFormId: "quotation_product_create_form_html",
        initialize : function(data){
            if(!data) data = {};

        },
        showCreateForm: function () {
            var self = this;
            var form = jQuery("#"+self.htmlFormContainerId);
            self.modal = form.modal({
                modalClass: 'magento',
                title: __("New Product"),
                type: 'slide',
                buttons: [{
                    text: jQuery.mage.__('Cancel'),
                    'class': 'action cancel',
                    click: function () {
                        this.closeModal();
                    }
                }, {
                    text: jQuery.mage.__('Add To Quote'),
                    'class': 'action primary',
                    click: function () {
                        self.submit();
                    }
                }]
            });
            self.modal.modal('openModal');
        },
        /**
         *
         * @returns {*}
         */
        isValid: function(){
            var self = this;
            var createForm = jQuery("#"+self.htmlFormId);
            createForm.validation();
            return createForm.validation('isValid');
        },
        /**
         *
         * @returns {AdminProduct}
         */
        submit: function(){
            var self = this;
            if(self.isValid()){
                var formModal = jQuery("#"+self.htmlFormContainerId);
                var createForm = jQuery("#"+self.htmlFormId);
                var params = createForm.serializeObject();
                params.add_custom_product = 1;
                params.reset_shipping = 1;
                quote.loadArea(["items", "info", 'totals', 'shipping_method'], true, params).done(function(){
                    formModal.modal('closeModal');
                    createForm[0].reset();
                });
            }
            return self;
        }
    };

    window.QuoteFormArea = Class.create();
    QuoteFormArea.prototype = {
        _name: null,
        _node: null,
        _parent: null,
        _callbackName: null,

        initialize: function(name, node, parent){
            if(!node)
                return;
            this._name = name;
            this._parent = parent;
            this._callbackName = node.callback;
            if (typeof this._callbackName == 'undefined') {
                this._callbackName = name + 'Loaded';
                node.callback = this._callbackName;
            }
            parent[this._callbackName] = parent[this._callbackName].wrap((function (proceed){
                proceed();
                this.onLoad();
            }).bind(this));

            this.setNode(node);
        },

        setNode: function(node){
            if (!node.callback) {
                node.callback = this._callbackName;
            }
            this.node = node;
        },

        onLoad: function(){
        }
    };

    window.ControlButton = Class.create();

    ControlButton.prototype = {
        _label: '',
        _node: null,

        initialize: function(label, id){
            this._label = label;
            this._node = new Element('button', {
                'class': 'action-secondary action-add',
                'type':  'button'
            });
            if (typeof id !== 'undefined') {
                this._node.setAttribute('id', id)
            }
        },

        onClick: function(){
        },

        insertIn: function(element, position){
            var node = Object.extend(this._node),
                content = {};
            node.observe('click', this.onClick);
            node.update('<span>' + this._label + '</span>');
            content[position] = node;
            Element.insert(element, content);
        }
    };

});

