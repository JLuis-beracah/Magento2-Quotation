<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Controller\Adminhtml\Quote;

class Save extends \Magestore\Quotation\Controller\Adminhtml\Quote\QuoteAbstract
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->createRedirectResult();
        $this->_initSession()->_processActionData('save');
        $quoteId = $this->getRequest()->getParam('quote_id');
        $this->_getSession()->clearStorage();
        $this->messageManager->addSuccess(__('The quote was saved successfully.'));
        if($quoteId){
            $resultRedirect->setPath('quotation/quote/edit', ['id' => $quoteId]);
        }else{
            $resultRedirect->setPath('quotation/quote');
        }
        return $resultRedirect;
    }
}
