<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Quotation\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class CheckoutCartUpdateItemsAfter implements ObserverInterface
{

    /**
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer)
    {
        $cart = $observer->getEvent()->getCart();
        $infoObject = $observer->getEvent()->getInfo();
        if($infoObject){
            $updateData = $infoObject->getData();
            if(!empty($updateData)){
                foreach ($updateData as $itemId => $itemInfo) {
                    $item = $cart->getQuote()->getItemById($itemId);
                    if (!$item) {
                        continue;
                    }
                    if(!empty($itemInfo['remark'])) {
                        $item->setAdditionalData($itemInfo['remark']);
                    }
                }
            }
        }
    }
}