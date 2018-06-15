<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Adminhtml\Quote\Edit;

use Magestore\Quotation\Model\Source\Quote\Status as QuoteStatus;
use Magestore\Quotation\Api\Data\QuoteCommentHistoryInterface;

/**
 * Class Comment
 * @package Magestore\Quotation\Block\Adminhtml\Quote\Edit
 */
class Comment extends \Magestore\Quotation\Block\Adminhtml\Quote\Edit\AbstractEdit
{
    /**
     * @var \Magento\Sales\Helper\Admin
     */
    private $adminHelper;

    /**
     * Comment constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Registry $registry
     * @param \Magestore\Quotation\Model\BackendSession $quoteSession
     * @param \Magestore\Quotation\Model\BackendCart $quoteCart
     * @param \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Registry $registry,
        \Magestore\Quotation\Model\BackendSession $quoteSession,
        \Magestore\Quotation\Model\BackendCart $quoteCart,
        \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $registry, $quoteSession, $quoteCart, $quotationManagement, $data);
        $this->adminHelper = $adminHelper;
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $onclick = "submitAndReloadArea($('quotation_quote_comment_history_block').parentNode, '" . $this->getSubmitUrl() . "')";
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            ['label' => __('Submit Comment'), 'class' => 'action-save action-secondary', 'onclick' => $onclick]
        );
        $this->setChild('submit_button', $button);
        return parent::_prepareLayout();
    }

    /**
     * Get stat uses
     *
     * @return array
     */
    public function getStatuses()
    {
        return $this->quotationManagement->getChangeAbleStatus($this->getQuote());
    }

    /**
     * Check allow to send quote comment email
     *
     * @return bool
     */
    public function canSendCommentEmail()
    {
        return true;
    }


    /**
     * Check allow to add comment
     *
     * @return bool
     */
    public function canAddComment()
    {
        $requestStatus = $this->getQuote()->getRequestStatus();
        return (!in_array($requestStatus, [
            QuoteStatus::STATUS_NONE,
            QuoteStatus::STATUS_PENDING,
            QuoteStatus::STATUS_ORDERED
        ]));
    }

    /**
     * Submit URL getter
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('quotation/quote/addComment', ['quote_id' => $this->getQuote()->getId()]);
    }

    /**
     * @param \Magestore\Quotation\Model\Quote\Comment\History $history
     * @return bool
     */
    public function isCustomerNotificationNotApplicable(\Magestore\Quotation\Model\Quote\Comment\History $history)
    {
        return $history->isCustomerNotificationNotApplicable();
    }

    /**
     * @param array|string $data
     * @param null $allowedTags
     * @return string
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        return $this->adminHelper->escapeHtmlWithLinks($data, $allowedTags);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuoteStatusLabel(){
        $statues = QuoteStatus::getOptionArray();
        return $statues[$this->getQuote()->getRequestStatus()];
    }

    /**
     * @return \Magestore\Quotation\Model\ResourceModel\Quote\Comment\History\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCommentHistoryCollection(){
        $collection = $this->quotationManagement->getCommentHistory($this->getQuote());
        $collection->setOrder(QuoteCommentHistoryInterface::CREATED_AT, "desc");
        return $collection;
    }
}
