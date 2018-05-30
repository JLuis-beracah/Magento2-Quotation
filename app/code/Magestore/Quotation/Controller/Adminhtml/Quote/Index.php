<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Controller\Adminhtml\Quote;

/**
 * Class Index
 * @package Magestore\Quotation\Controller\Adminhtml\Quote
 */
class Index extends \Magestore\Quotation\Controller\Adminhtml\AbstractAction
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->createPageResult();
        $resultPage->setActiveMenu('Magestore_Quotation::quotation');
        $resultPage->addBreadcrumb(__('Quote Request'), __('Quote Request'));
        $resultPage->getConfig()->getTitle()->prepend(__('Quote Request'));
        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Quotation::quote_request');
    }
}
