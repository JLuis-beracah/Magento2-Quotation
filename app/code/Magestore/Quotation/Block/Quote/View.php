<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Quote;

use Magento\Customer\Model\Context;
use Magestore\Quotation\Api\Data\QuoteCommentHistoryInterface;

/**
 * Class View
 * @package Magestore\Quotation\Block\Quote
 */
class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'quote/view.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Http\Context
     * @since 100.2.0
     */
    protected $httpContext;

    /**
     * @var \Magestore\Quotation\Model\ResourceModel\Quote\Comment\History\CollectionFactory
     */
    protected $historyCollectionFactory;

    /**
     * View constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magestore\Quotation\Model\ResourceModel\Quote\Comment\History\CollectionFactory $historyCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magestore\Quotation\Model\ResourceModel\Quote\Comment\History\CollectionFactory $historyCollectionFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->httpContext = $httpContext;
        $this->_historyCollectionFactory = $historyCollectionFactory;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Quote # %1', $this->getQuote()->getEntityId()));
    }

    /**
     * Retrieve current quote model instance
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->_coreRegistry->registry('current_quote');
    }

    /**
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return $this->getUrl('*/*/history');
        }
        return $this->getUrl('*/*/');
    }

    /**
     * Return back title for logged in and guest users
     *
     * @return \Magento\Framework\Phrase
     */
    public function getBackTitle()
    {
        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return __('Back to My Quotes');
        }
        return __('View Another Quote');
    }

    /**
     * Return collection of quote comment history items.
     *
     * @return HistoryCollection
     */
    public function getStatusHistoryCollection()
    {
        $quote = $this->getQuote();
        $collection = $this->_historyCollectionFactory->create()->setQuoteFilter($quote)
            ->setOrder(QuoteCommentHistoryInterface::CREATED_AT, 'desc')
            ->setOrder(QuoteCommentHistoryInterface::ENTITY_ID, 'desc');
        if ($quote->getId()) {
            foreach ($collection as $status) {
                $status->setQuote($quote);
            }
        }
        return $collection;
    }

    /**
     * @return array
     */
    public function getVisibleCommentHistory(){
        $history = [];
        foreach ($this->getStatusHistoryCollection() as $status) {
            if (!$status->isDeleted() && $status->getComment() && $status->getIsVisibleOnFront()) {
                $history[] = $status;
            }
        }
        return $history;
    }

    /**
     * @return string
     */
    public function getSubmitCommentUrl()
    {
        return $this->getUrl('quotation/quote/addComment');
    }
}
