<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Controller\Adminhtml\Quote;

use Magestore\Quotation\Model\Source\Quote\Status as QuoteStatus;

/**
 * Class Edit
 * @package Magestore\Quotation\Controller\Adminhtml\Quote
 */
class Edit extends \Magestore\Quotation\Controller\Adminhtml\AbstractAction
{

    /**
     * @return $this|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $resultRedirect = $this->createRedirectResult();
        $model = $this->_objectManager->create('Magento\Quote\Model\Quote');
        $registryObject = $this->_objectManager->get('Magento\Framework\Registry');
        $isValidRequest = false;
        if ($id) {
            $model = $model->load($id);
            if ($model->getId()) {
                $isValidRequest = true;
            }
        }
        if(!$isValidRequest){
            $this->messageManager->addErrorMessage(__('This quote request no longer exists.'));
            return $resultRedirect->setPath('quotation/*/', ['_current' => true]);
        }
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        if($model->getRequestStatus() == QuoteStatus::STATUS_PROCESSED){
            $this->quotationManagement->isExpired($model);
            $model = $model->load($id);
        }
        $registryObject->register('current_quote_request', $model);
        $resultPage = $this->createPageResult();
        $resultPage->getConfig()->getTitle()->prepend(__('Quote #%1', $model->getId()));
        return $resultPage;
    }

}