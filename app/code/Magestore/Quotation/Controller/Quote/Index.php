<?php
/**
 *
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Quotation\Controller\Quote;

class Index extends \Magento\Checkout\Controller\Cart\Index
{
    /**
     * Shopping cart display action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Request For Quote'));
        return $resultPage;
    }
}
