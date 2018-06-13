<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Quotation\Model\CustomProduct;

/**
 * Class Price
 * @package Magestore\Quotation\Model\CustomProduct
 */
class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    /**
     * 
     * @param \Magento\Catalog\Model\Product $product
     * @param float $qty
     * @param float $finalPrice
     * @return _applyOptionsPrice
     */
    protected function _applyOptionsPrice($product, $qty, $finalPrice)
    {
        if ($amount = $product->getCustomOption('price')) {
            $finalPrice = $amount->getValue();
        }
        return parent::_applyOptionsPrice($product, $qty, $finalPrice);
    }
}
