<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magestore\Quotation\Model\CustomProduct\Type as CustomProductType;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var
     */
    protected $_appState;
    /**
     * @var AttributeSetFactory
     */
    protected $attributeSetFactory;
    /**
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;
    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory
     */
    protected $_websiteCollectionFactory;
    /**
     * {@inheritdoc}
     */
    protected $_product;
    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;


    /**
     * UpgradeData constructor.
     * @param \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\App\State $appState
     * @param AttributeSetFactory $attributeSetFactory
     * @param CategorySetupFactory $categorySetupFactory
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\App\State $appState,
        AttributeSetFactory $attributeSetFactory,
        CategorySetupFactory $categorySetupFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ){
        $this->attributeSetFactory = $attributeSetFactory;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->_product = $product;
        $this->_appState = $appState;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProductModel() {
        return $this->_product;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $version = $this->productMetadata->getVersion();
        try{
            if(version_compare($version, '2.2.0', '>=')) {
                $this->_appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
            } else {
                $this->_appState->setAreaCode('admin');
            }
        } catch(\Exception $e) {
            $this->_appState->getAreaCode();
        }

        $product = $this->getProductModel();
        if (!$product->getIdBySku(CustomProductType::DEFAULT_CUSTOM_PRODUCT_SKU)) {
            $websiteIds = $this->_websiteCollectionFactory->create()
                ->addFieldToFilter('website_id', array('neq' => 0))
                ->getAllIds();

            $attributeSet = $this->attributeSetFactory->create();
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
            $attributeSetId = $this->attributeSetFactory->create()
                ->getCollection()
                ->setEntityTypeFilter($entityTypeId)
                ->addFieldToFilter('attribute_set_name', 'Quotation_Custom_Product_Attribute_Set')
                ->getFirstItem()
                ->getAttributeSetId();
            if (!$attributeSetId) {
                $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
                $data = [
                    'attribute_set_name' => 'Quotation_Custom_Product_Attribute_Set', // define custom attribute set name here
                    'entity_type_id' => $entityTypeId,
                    'sort_order' => 200,
                ];
                $attributeSet->setData($data);
                $attributeSet->validate();
                $attributeSet->save();
                $attributeSet->initFromSkeleton($attributeSetId);
                $attributeSet->save();
                $attributeSetId = $attributeSet->getId();
            }


            $product->setAttributeSetId($attributeSetId)
                ->setTypeId(CustomProductType::DEFAULT_CUSTOM_PRODUCT_TYPE_ID)
                ->setStoreId(0)
                ->setSku(CustomProductType::DEFAULT_CUSTOM_PRODUCT_SKU)
                ->setWebsiteIds($websiteIds)
                ->setStockData(array(
                    'manage_stock' => 0,
                    'use_config_manage_stock' => 0,
                ));
            $product->addData(array(
                'name' => 'Quotation Custom Product',
                'weight' => 1,
                'status' => 1,
                'visibility' => 1,
                'price' => 0,
                'description' => 'Quotation Custom Product',
                'short_description' => 'Quotation Custom Product',
                'quantity_and_stock_status' => array()
            ));
            if (!is_array($errors = $product->validate())) {
                try {
                    $product->save();
                } catch (\Exception $e) {
                    return $this;
                }
            }
        }
        $setup->endSetup();
    }

}
