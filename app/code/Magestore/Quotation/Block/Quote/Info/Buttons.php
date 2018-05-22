<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Block of links in Quote view page
 */
namespace Magestore\Quotation\Block\Quote\Info;

/**
 * @api
 * @since 100.0.2
 */
class Buttons extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'quote/info/buttons.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
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
     * Retrieve current quote model instance
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->_coreRegistry->registry('current_quote');
    }
}
