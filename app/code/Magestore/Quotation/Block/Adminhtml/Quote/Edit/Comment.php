<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Adminhtml\Quote\Edit;

/**
 * Class Comment
 * @package Magestore\Quotation\Block\Adminhtml\Quote\Edit
 */
class Comment extends \Magestore\Quotation\Block\Adminhtml\Quote\Edit\AbstractEdit
{
    /**
     * Data Form object
     *
     * @var \Magento\Framework\Data\Form
     */
    protected $_form;

    /**
     * Get header css class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-comment';
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Quote Comment');
    }

    /**
     * Get comment note
     *
     * @return string
     */
    public function getCommentNote()
    {
        return $this->escapeHtml($this->getQuote()->getCustomerNote());
    }
}
