<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Quote\Email\Items\Quote;

use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Class DefaultQuote
 * @package Magestore\Quotation\Block\Quote\Email\Items\Quote
 */
class DefaultQuote extends \Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder
{
    /**
     * Retrieve current quote model instance
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->getItem()->getQuote();
    }

    /**
     * @return array
     */
    public function getItemOptions()
    {
        $result = [];
        $item = $this->getItem();
        $options = $item->getProductOrderOptions();
        if (!$options) {
            $options = $item->getProduct()->getTypeInstance()->getOrderOptions($item->getProduct());
        }
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

    /**
     * @param mixed $item
     * @return mixed
     */
    public function getSku($item)
    {
        if ($item->getOptionByCode('simple_sku')) {
            return $item->getOptionByCode('simple_sku');
        } else {
            return $item->getSku();
        }
    }

    /**
     * Get the html for item price
     *
     * @param QuoteItem $item
     * @return string
     */
    public function getQuoteItemPrice(QuoteItem $item)
    {
        $block = $this->getLayout()->getBlock('item_price');
        $block->setItem($item);
        return $block->toHtml();
    }
}
