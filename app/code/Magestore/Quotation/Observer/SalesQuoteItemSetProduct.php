<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Quotation\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magestore\Quotation\Model\CustomProduct\Type as CustomProductType;

class SalesQuoteItemSetProduct implements ObserverInterface
{
    /**
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if (strpos($product->getSku(), CustomProductType::DEFAULT_CUSTOM_PRODUCT_SKU) === false) {
            return;
        }
        $tax_class_id = $product->getCustomOption('tax_class_id');
        if ($tax_class_id && $tax_class_id->getValue()) {
            $item = $observer->getEvent()->getQuoteItem();
            $item->getProduct()->setTaxClassId($tax_class_id->getValue());
        }
        $name = $product->getCustomOption('name');
        if ($name && $name->getValue()) {
            $item = $observer->getEvent()->getQuoteItem();
            $item->setName($name->getValue());
        }
        $name = $product->getCustomOption('weight');
        if ($name && $name->getValue()) {
            $item = $observer->getEvent()->getQuoteItem();
            $item->setWeight($name->getValue());
        }
        $description = $product->getCustomOption('description');
        if ($description && $description->getValue()) {
            $item = $observer->getEvent()->getQuoteItem();
            $item->setDescription($description->getValue());
        }
    }
}