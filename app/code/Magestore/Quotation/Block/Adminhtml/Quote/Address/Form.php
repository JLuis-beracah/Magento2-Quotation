<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Adminhtml\Quote\Address;

/**
 * Class Form
 * @package Magestore\Quotation\Block\Adminhtml\Quote\Address
 */
class Form extends \Magento\Sales\Block\Adminhtml\Order\Address\Form
{
    /**
     * Address form template
     *
     * @var string
     */
    protected $_template = 'quote/address/form.phtml';

    /**
     * Quote address getter
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    protected function _getAddress()
    {
        return $this->_coreRegistry->registry('current_quote_address');
    }

    /**
     * Define form attributes (id, method, action)
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $this->_form->setId('edit_form');
        $this->_form->setMethod('post');
        $this->_form->setAction(
            $this->getUrl('quotation/quote/addressSave', ['address_id' => $this->_getAddress()->getId()])
        );
        $this->_form->setUseContainer(true);
        return $this;
    }

    /**
     * Form header text getter
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Quote Address Information');
    }
}
