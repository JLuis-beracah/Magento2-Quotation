<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Adminhtml\Quote\Edit;

/**
 * Class Header
 */
class Header extends \Magestore\Quotation\Block\Adminhtml\Quote\Edit\AbstractEdit
{

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if ($this->getQuote()->getId()) {
            return __('Quote #%1', $this->getQuote()->getId());
        }
        return __('New Quote');
    }
}
