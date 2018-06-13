<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model\CustomProduct;

/**
 * Class Type
 * @package Magestore\Quotation\Model\CustomProduct
 */
class Type extends \Magento\Catalog\Model\Product\Type\AbstractType
{
    const DEFAULT_CUSTOM_PRODUCT_SKU = "quotation-custom-product";
    const DEFAULT_CUSTOM_PRODUCT_TYPE_ID = "quotation_custom_product";

    /**
     * 
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @return array|string
     */
    public function prepareForCart(\Magento\Framework\DataObject $buyRequest, $product)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        $result = parent::prepareForCart($buyRequest, $product);
        if (is_string($result)) {
            return $result;
        }
        reset($result);
        $product = current($result);
        $result = $this->_prepareCustomProduct($buyRequest, $product, null);
        return $result;
    }
    
    /**
     * 
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string $processMode
     * @return array|string
     */
    protected function _prepareProduct(\Magento\Framework\DataObject $buyRequest, $product, $processMode)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        $result = parent::_prepareProduct($buyRequest, $product, $processMode);
        if (is_string($result)) {
            return $result;
        }
        reset($result);
        $product = current($result);
        $result = $this->_prepareCustomProduct($buyRequest, $product);
        return $result;
    }
    
    /**
     * 
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string $processMode
     * @return array
     */
    protected function _prepareCustomProduct(\Magento\Framework\DataObject $buyRequest, $product, $processMode = null)
    {
        $options = $buyRequest->getData('options');
        if($options && isset($options['is_virtual'])){
            $product->addCustomOption('is_virtual', $options['is_virtual']);
        }
        if($options && isset($options['tax_class_id'])){
            $product->addCustomOption('tax_class_id', $options['tax_class_id']);
        }
        if($options && isset($options['name'])){
            $product->addCustomOption('name', $options['name']);
        }
        if($options && isset($options['price'])){
            $product->addCustomOption('price', $options['price']);
        }
        if($options && isset($options['description'])){
            $product->addCustomOption('description', $options['description']);
        }
        return array($product);
    }

    /**
     * 
     * @param \Magento\Catalog\Model\Product $product
     * @return boolean
     */
    public function isVirtual($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        if ($isVirtual = $product->getCustomOption('is_virtual')) {
            return (bool) $isVirtual->getValue();
        }
        return true;
    }
    
    /**
     * 
     * @param \Magento\Catalog\Model\Product $product
     * @return boolean
     */
    public function isSalable($product = null)
    {
        return ($this->isAdminArea())?parent::isSalable($product):false;
    }
    
    /**
     * 
     * @param \Magento\Catalog\Model\Product $product
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAdminArea() {
        /** @var \Magento\Framework\ObjectManagerInterface $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\App\State $state */
        $state =  $om->get('Magento\Framework\App\State');
        return 'adminhtml' === $state->getAreaCode();
    }
}