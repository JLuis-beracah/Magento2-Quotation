define([
    'jquery'
], function(jQuery){
    return function(originalWidget){
        jQuery.widget(
            'mage.catalogAddToCart',
            jQuery['mage']['catalogAddToCart'],
            {
                submitForm: function (form) {
                    var addToQuotationButton, self = this;
                    addToQuotationButton = jQuery(form).find(".action.toquotation");
                    if((addToQuotationButton.length > 0) && (addToQuotationButton.hasClass("submitted"))){
                        self.element.off('submit');
                        addToQuotationButton.prop('disabled', true);
                        addToQuotationButton.addClass("disabled");
                        form.submit();
                    }else{
                        self._super(form);
                    }
                },
            }
        );

        return jQuery['mage']['catalogAddToCart'];
    };
});