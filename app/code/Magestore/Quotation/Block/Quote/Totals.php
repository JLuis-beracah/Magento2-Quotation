<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Quote;

use Magento\Quote\Model\Quote;

/**
 * Class Totals
 * @package Magestore\Quotation\Block\Quote
 */
class Totals extends \Magento\Sales\Block\Order\Totals
{
    /**
     * @var Quote|null
     */
    protected $_quote = null;

    /**
     * @var \Magestore\Quotation\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magestore\Quotation\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
        $this->helper = $helper;
    }

    /**
     * Get quote object
     *
     * @return Quote
     */
    public function getQuote()
    {
        if ($this->_quote === null) {
            if ($this->hasData('quote')) {
                $this->_quote = $this->_getData('quote');
            } elseif ($this->_coreRegistry->registry('current_quote')) {
                $this->_quote = $this->_coreRegistry->registry('current_quote');
            } elseif ($this->getParentBlock()->getQuote()) {
                $this->_quote = $this->getParentBlock()->getQuote();
            }
        }
        return $this->_quote;
    }

    /**
     * @param Quote $quote
     * @return $this
     */
    public function setQuote($quote)
    {
        $this->_quote = $quote;
        return $this;
    }

    /**
     * Get totals source object
     *
     * @return Quote
     */
    public function getSource()
    {
        return $this->getQuote();
    }

    /**
     * Initialize order totals array
     *
     * @return $this
     */
    protected function _initTotals()
    {
        $source = $this->getSource();

        $this->_totals = [];
        $this->_totals['subtotal'] = new \Magento\Framework\DataObject(
            ['code' => 'subtotal', 'value' => $source->getSubtotal(), 'label' => __('Subtotal')]
        );

        if($source->getShippingAddress()->getShippingMethod()){
            $this->_totals['shipping'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'shipping',
                    'field' => 'shipping',
                    'strong' => true,
                    'value' => $source->getShippingAddress()->getShippingAmount(),
                    'label' => __('Shipping & Handling (%1)', $source->getShippingAddress()->getShippingDescription()),
                ]
            );
        }

        $this->_totals['grand_total'] = new \Magento\Framework\DataObject(
            [
                'code' => 'grand_total',
                'field' => 'grand_total',
                'strong' => true,
                'value' => $source->getGrandTotal(),
                'label' => __('Grand Total'),
            ]
        );

        /**
         * Base grandtotal
         */
        if ($this->isCurrencyDifferent()) {
            $this->_totals['base_grandtotal'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'base_grandtotal',
                    'value' => $this->helper->formatQuoteBasePrice($source, $source->getBaseGrandTotal()),
                    'label' => __('Grand Total to be Charged'),
                    'is_formated' => true,
                ]
            );
        }
        return $this;
    }

    /**
     * Format total value based on order currency
     *
     * @param   \Magento\Framework\DataObject $total
     * @return  string
     */
    public function formatValue($total)
    {
        if (!$total->getIsFormated()) {
            return $this->helper->formatQuotePrice($this->getQuote(),$total->getValue());
        }
        return $total->getValue();
    }

    /**
     * @return bool
     */
    public function isCurrencyDifferent(){
        $quote = $this->getQuote();
        return ($quote->getQuoteCurrencyCode() != $quote->getBaseCurrencyCode())?true:false;
    }
}
