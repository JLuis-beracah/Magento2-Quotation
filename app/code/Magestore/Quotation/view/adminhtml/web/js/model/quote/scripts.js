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
                    searchButton = new ControlButton(jQuery.mage.__('Add Product By SKU'), searchButtonId),
                    searchAreaId = this.getAreaId('search');
                searchButton.onClick = function() {
                    // $(searchAreaId).show();
                    // var el = this;
                    // window.setTimeout(function () {
                    //     el.remove();
                    // }, 10);
                };

                if (jQuery('#' + this.getAreaId('items')).is(':visible')) {
                    this.dataArea.onLoad = this.dataArea.onLoad.wrap(function(proceed) {
                        proceed();
                        this._parent.itemsArea.setNode($(this._parent.getAreaId('items')));
                        this._parent.itemsArea.onLoad();
                    });

                    this.itemsArea.onLoad = this.itemsArea.onLoad.wrap(function(proceed) {
                        proceed();
                        if ($(searchAreaId) && !$(searchAreaId).visible() && !$(searchButtonId)) {
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

        setCurrencyId : function(id){
            this.currencyId = id;
            this.loadArea(['data', 'info'], true);
        },

        setCurrencySymbol : function(symbol){
            this.currencySymbol = symbol;
        },

        addProduct : function(id){
            this.loadArea(['items', 'info'], true, {add_product:id});
        },

        removeQuoteItem : function(id){
            this.loadArea(['items', 'info'], true,
                {remove_item:id, from:'quote'});
        },


        dataHide : function(){
            this.hideArea('data');
        },

        dataShow : function(){
            if ($('submit_quote_top_button')) {
                $('submit_quote_top_button').show();
            }
            this.showArea('data');
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
            self.loadArea(["items", "info"], true, fieldsPrepare);
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
                        deferred.resolve();
                    }.bind(this)
                });
            }
            else {
                new Ajax.Request(url, {
                    parameters:params,
                    loaderArea: indicator,
                    onSuccess: function(transport) {
                        deferred.resolve();
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
        _realSend: function(message){
            var self = this;
            confirm({
                content: message,
                actions: {
                    confirm: function() {
                        var params = {quote_request_action: 'send'};
                        self.loadArea(["items", "info"], true, params).done(function(){
                            disableElements('save_as_draft');
                            disableElements('decline');
                        });
                    },
                    cancel: function() {

                    }
                }
            });
        },
        send: function(message){
            var self = this;
            if (this.quoteItemChanged) {
                var self = this;
                confirm({
                    content: jQuery.mage.__('You have item changes'),
                    actions: {
                        confirm: function() {
                            self._realSend(message);
                        },
                        cancel: function() {
                            self.itemsUpdate();
                        }
                    }
                });
            } else {
                self._realSend(message);
            }
        },

        decline: function(message){
            var self = this;
            confirm({
                content: message,
                actions: {
                    confirm: function() {
                        var params = {quote_request_action: 'decline'};
                        self.loadArea(["items", "info"], true, params).done(function(){
                            disableElements('save_as_draft');
                            disableElements('decline');
                            disableElements('send');
                        });
                    },
                    cancel: function() {

                    }
                }
            });
        }
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
                    text: jQuery.mage.__('Create'),
                    'class': 'action primary',
                    click: function () {
                        self.submit(false);
                    }
                }, {
                    text: jQuery.mage.__('Create And Add To Quote'),
                    'class': 'action primary',
                    click: function () {
                        self.submit(true);
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
         * @param addToQuote
         * @returns {AdminProduct}
         */
        submit: function(addToQuote){
            var self = this;
            if(self.isValid()){
                var formModal = jQuery("#"+self.htmlFormContainerId);
                var createForm = jQuery("#"+self.htmlFormId);
                var params = createForm.serializeObject();
                params.create_product = 1;
                if(addToQuote){
                    params.add_to_quote = 1;
                }
                quote.loadArea(["items", "info"], true, params).done(function(){
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

