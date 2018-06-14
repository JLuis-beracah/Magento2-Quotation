<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Plugin\SalesRule;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Framework\DataObject;

/**
 * Class Validator
 * @package Magestore\Quotation\Plugin\SalesRule
 */
class Validator
{
    /**
     * @var \Magestore\Quotation\Helper\Data
     */
    protected $helper;

    /**
     * Validator constructor.
     * @param \Magestore\Quotation\Helper\Data $helper
     */
    public function __construct(
        \Magestore\Quotation\Helper\Data $helper
    ){
        $this->helper = $helper;
    }

    /**
     * @param \Magento\SalesRule\Model\Validator $validator
     * @param \Magento\SalesRule\Model\Validator $result
     * @param AbstractItem $item
     * @return \Magento\SalesRule\Model\Validator
     */
    public function afterProcess(\Magento\SalesRule\Model\Validator $validator, $result, AbstractItem $item)
    {
        $percentage = $item->getAdminDiscountPercentage();
        if(!$percentage){
            return $result;
        }
        $qty = $item->getQty();

        $itemPrice = $validator->getItemPrice($item);
        $baseItemPrice = $validator->getItemBasePrice($item);
        $originalPrice = $validator->getItemOriginalPrice($item);
        $baseOriginalPrice = $validator->getItemBaseOriginalPrice($item);
        $originalTotalPrice = floatval($qty * $originalPrice );
        $baseOriginalTotalPrice = floatval($qty * $baseOriginalPrice );

        $adminDiscountAmount = floatval($qty * $itemPrice * $percentage / 100);
        $baseAdminDiscountAmount = floatval($qty * $baseItemPrice * $percentage / 100);
        $originalAdminDiscountAmount = floatval($qty * $originalPrice * $percentage / 100);
        $baseAdminOriginalDiscountAmount = floatval($qty * $baseOriginalPrice * $percentage / 100);

        $discountData = new DataObject([
            'percent' => $percentage,
            'amount' => 0,
            'base_amount' => 0,
            'original_amount' => 0,
            'base_original_amount' => 0
        ]);
        if($this->canUseAdminDiscountWithPromotion()){
            $discountAmount = $item->getDiscountAmount();
            $baseDiscountAmount = $item->getBaseDiscountAmount();
            $originalDiscountAmount = $item->getOriginalDiscountAmount();
            $baseOriginalDiscountAmount = $item->getBaseOriginalDiscountAmount();
            $newAmount = $discountAmount + $adminDiscountAmount;
            $newBaseAmount = $baseDiscountAmount + $baseAdminDiscountAmount;
            $newOriginalDiscountAmount = $originalDiscountAmount + $originalAdminDiscountAmount;
            $newBaseOriginalDiscountAmount = $baseOriginalDiscountAmount + $baseAdminOriginalDiscountAmount;

            $newAmount = ($newAmount <= $originalTotalPrice)?$newAmount:$originalTotalPrice;
            $newBaseAmount = ($newBaseAmount <= $baseAdminOriginalDiscountAmount)?$newBaseAmount:$baseOriginalTotalPrice;
            $newOriginalDiscountAmount = ($newOriginalDiscountAmount <= $originalTotalPrice)?$newOriginalDiscountAmount:$originalTotalPrice;
            $newBaseOriginalDiscountAmount = ($newBaseOriginalDiscountAmount <= $baseAdminOriginalDiscountAmount)?$newBaseOriginalDiscountAmount:$baseOriginalTotalPrice;
            $discountData->setAmount($newAmount);
            $discountData->setBaseAmount($newBaseAmount);
            $discountData->setOriginalAmount($newOriginalDiscountAmount);
            $discountData->setBaseOriginalAmount($newBaseOriginalDiscountAmount);
        }else{
            $discountData->setAmount($adminDiscountAmount);
            $discountData->setBaseAmount($baseAdminDiscountAmount);
            $discountData->setOriginalAmount($originalAdminDiscountAmount);
            $discountData->setBaseOriginalAmount($baseAdminOriginalDiscountAmount);
        }
        $this->setDiscountData($discountData, $item);
        return $result;
    }

    /**
     * @return bool
     */
    public function canUseAdminDiscountWithPromotion(){
        return boolval($this->helper->getStoreConfig("quotation/general/use_admin_discount_with_promotion_rules"));
    }

    /**
     * @param DataObject $discountData
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return $this
     */
    protected function setDiscountData(DataObject $discountData, AbstractItem $item)
    {
        $item->setDiscountAmount($discountData->getAmount());
        $item->setBaseDiscountAmount($discountData->getBaseAmount());
        $item->setOriginalDiscountAmount($discountData->getOriginalAmount());
        $item->setBaseOriginalDiscountAmount($discountData->getBaseOriginalAmount());
        $item->setDiscountPercent($discountData->getPercent());
        return $this;
    }
}