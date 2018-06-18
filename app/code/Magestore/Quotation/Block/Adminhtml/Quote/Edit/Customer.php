<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Adminhtml\Quote\Edit;

/**
 * Class Customer
 * @package Magestore\Quotation\Block\Adminhtml\Quote\Edit
 */
class Customer extends \Magestore\Quotation\Block\Adminhtml\Quote\Edit\AbstractEdit
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quotation_quote_edit_customer');
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Please select a customer');
    }

    /**
     * Get buttons html
     *
     * @return string
     */
    public function getButtonsHtml()
    {
        if ($this->_authorization->isAllowed('Magento_Customer::manage')) {
            $addButtonData = [
                'label' => __('Create New Customer'),
                'onclick' => 'quote.setCustomerId(false)',
                'class' => 'primary',
            ];
            return $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)
                ->setData($addButtonData)
                ->toHtml();
        }
        return '';
    }
}
