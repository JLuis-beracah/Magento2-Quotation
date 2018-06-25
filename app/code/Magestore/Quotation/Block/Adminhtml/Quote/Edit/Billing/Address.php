<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Adminhtml\Quote\Edit\Billing;

/**
 * Class Address
 * @package Magestore\Quotation\Block\Adminhtml\Quote\Edit\Billing
 */
class Address extends \Magestore\Quotation\Block\Adminhtml\Quote\Edit\Form\Address
{
    /**
     * Return header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Billing Address');
    }

    /**
     * Return Header CSS Class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-billing-address';
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $this->setJsVariablePrefix('billingAddress');
        parent::_prepareForm();

        $this->_form->addFieldNameSuffix('quote[billing_address]');
        $this->_form->setHtmlNamePrefix('quote[billing_address]');
        $this->_form->setHtmlIdPrefix('quote-billing_address_');

        return $this;
    }

    /**
     * Return Form Elements values
     *
     * @return array
     */
    public function getFormValues()
    {
        return $this->getEditQuoteModel()->getBillingAddress()->getData();
    }

    /**
     * Return customer address id
     *
     * @return int|bool
     */
    public function getAddressId()
    {
        return $this->getEditQuoteModel()->getBillingAddress()->getCustomerAddressId();
    }

    /**
     * Return billing address object
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getAddress()
    {
        return $this->getEditQuoteModel()->getBillingAddress();
    }
}
