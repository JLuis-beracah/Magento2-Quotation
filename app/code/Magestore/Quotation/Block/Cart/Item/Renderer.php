<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Cart\Item;

use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Class Renderer
 * @package Magestore\Quotation\Block\Cart\Item
 */
class Renderer extends \Magento\Checkout\Block\Cart\Item\Renderer
{

    /**
     * Return the unit price html
     *
     * @param AbstractItem $item
     * @return string
     */
    public function getUnitPriceHtml(AbstractItem $item)
    {
        /** @var Renderer $block */
        $block = $this->getLayout()->getBlock('quotation.item.price.unit');
        $block->setItem($item);
        return $block->toHtml();
    }

    /**
     * Return row total html
     *
     * @param AbstractItem $item
     * @return string
     */
    public function getRowTotalHtml(AbstractItem $item)
    {
        /** @var Renderer $block */
        $block = $this->getLayout()->getBlock('quotation.item.price.row');
        $block->setItem($item);
        return $block->toHtml();
    }

    /**
     * Get list of all options for product
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getOptionList()
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
}
