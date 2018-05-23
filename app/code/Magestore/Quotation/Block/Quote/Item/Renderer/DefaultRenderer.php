<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magestore\Quotation\Block\Quote\Item\Renderer;

/**
 * Class DefaultRenderer
 * @package Magestore\Quotation\Block\Quote\Item\Renderer
 */
class DefaultRenderer extends \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
{
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * DefaultRenderer constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        array $data = []
    ) {
        parent::__construct($context, $string, $productOptionFactory, $data);
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Retrieve current quote model instance
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if(!$this->quote){
            $quoteId = $this->getItem()->getQuoteId();
            $quote = $this->quoteFactory->create();
            $quote->load($quoteId);
            $this->quote = $quote;
        }
        return $this->quote;
    }

    /**
     * @return array
     */
    public function getItemOptions()
    {
        $result = [];
        $options = $this->getItem()->getOptions();
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }
        return $result;
    }
}