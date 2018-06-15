<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Adminhtml\Quote\Edit\Shipping;

/**
 * Class Method
 * @package Magestore\Quotation\Block\Adminhtml\Quote\Edit\Shipping
 */
class Method extends \Magestore\Quotation\Block\Adminhtml\Quote\Edit\AbstractEdit
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quotation_quote_edit_shipping_method');
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Shipping Method');
    }

    /**
     * Get header css class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-shipping-method';
    }
}
