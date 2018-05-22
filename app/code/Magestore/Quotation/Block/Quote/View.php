<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Quote;

use Magento\Customer\Model\Context;

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
     * View constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->httpContext = $httpContext;
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
}
