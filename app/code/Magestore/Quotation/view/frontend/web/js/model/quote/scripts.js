/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    "jquery",
    'Magento_Ui/js/modal/confirm',
    "mage/translate",
    "prototype",
    'Magento_Ui/js/lib/view/utils/async'
], function(jQuery, confirm){

    window.CartQuote = new Class.create();

    CartQuote.prototype = {
        initialize : function(){

        },
        itemsOnchangeBind : function(){
            var elems = $('form-validate').select('input', 'select', 'textarea');
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

        submit : function()
        {
            var self = this;
            if (this.quoteItemChanged) {
                confirm({
                    content: jQuery.mage.__('You have item changes'),
                    actions: {
                        confirm: function() {
                            self._realSubmit();
                        },
                        cancel: function() {
                            jQuery('#form-validate').submit();
                        }
                    }
                });
            } else {
                self._realSubmit();
            }
        },

        _realSubmit: function () {
            jQuery("#quotation-info-form").submit();
        }
    };

});

