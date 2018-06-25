<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Adminhtml\Quote\Edit\Shipping;

/**
 * Class Address
 * @package Magestore\Quotation\Block\Adminhtml\Quote\Edit\Shipping
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
        return __('Shipping Address');
    }

    /**
     * Return Header CSS Class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-shipping-address';
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $this->setJsVariablePrefix('shippingAddress');
        parent::_prepareForm();

        $this->_form->addFieldNameSuffix('quote[shipping_address]');
        $this->_form->setHtmlNamePrefix('quote[shipping_address]');
        $this->_form->setHtmlIdPrefix('quote-shipping_address_');

        return $this;
    }

    /**
     * Return is shipping address flag
     *
     * @return true
     */
    public function getIsShipping()
    {
        return true;
    }

    /**
     * Same as billing address flag
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsAsBilling()
    {
        return $this->getEditQuoteModel()->getShippingAddress()->getSameAsBilling();
    }

    /**
     * Saving shipping address must be turned off, when it is the same as billing
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getDontSaveInAddressBook()
    {
        return $this->getIsAsBilling();
    }

    /**
     * Return Form Elements values
     *
     * @return array
     */
    public function getFormValues()
    {
        return $this->getAddress()->getData();
    }

    /**
     * Return customer address id
     *
     * @return int|bool
     */
    public function getAddressId()
    {
        return $this->getAddress()->getCustomerAddressId();
    }

    /**
     * Return address object
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getAddress()
    {
        if ($this->getIsAsBilling()) {
            $address = $this->getEditQuoteModel()->getBillingAddress();
        } else {
            $address = $this->getEditQuoteModel()->getShippingAddress();
        }
        return $address;
    }

    /**
     * Return is address disabled flag
     * Return true is the quote is virtual
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsDisabled()
    {
        return $this->getQuote()->isVirtual();
    }
}
