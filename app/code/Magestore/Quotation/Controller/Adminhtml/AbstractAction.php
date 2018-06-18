<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\Quotation\Controller\Adminhtml;

/**
 * Class AbstractAction
 * @package Magestore\Quotation\Controller\Adminhtml
 */
abstract class AbstractAction extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @var \Magestore\Quotation\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magestore\Quotation\Api\QuotationManagementInterface
     */
    protected $quotationManagement;

    /**
     * @var \Magestore\Quotation\Model\BackendCart
     */
    protected $backendCart;

    /**
     * @var \Magestore\Quotation\Model\BackendSession
     */
    protected $backendSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * AbstractAction constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magestore\Quotation\Helper\Data $helper
     * @param \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement
     * @param \Magestore\Quotation\Model\BackendCart $backendCart
     * @param \Magestore\Quotation\Model\BackendSession $backendSession
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magestore\Quotation\Helper\Data $helper,
        \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement,
        \Magestore\Quotation\Model\BackendCart $backendCart,
        \Magestore\Quotation\Model\BackendSession $backendSession,
        \Magento\Framework\Registry $registry
    ){
        parent::__construct($context);
        $this->helper = $helper;
        $this->quotationManagement = $quotationManagement;
        $this->backendCart = $backendCart;
        $this->backendSession = $backendSession;
        $this->registry = $registry;
        $this->resultFactory = $context->getResultFactory();
    }

    /**
     * @param $data
     * @return mixed
     */
    public function createJsonResult($data){
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        return $resultJson->setData($data);
    }

    /**
     * @return mixed
     */
    public function createPageResult(){
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        return $resultPage;
    }

    /**
     * @return mixed
     */
    public function createForwardResult(){
        $resultForward = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD);
        return $resultForward;
    }

    /**
     * @return mixed
     */
    public function createRedirectResult(){
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        return $resultRedirect;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function createRawResult(){
        $resultRaw = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        return $resultRaw;
    }

    /**
     * @return \Magestore\Quotation\Model\BackendSession|mixed
     */
    protected function _getSession()
    {
        return $this->backendSession;
    }

    /**
     * @return \Magestore\Quotation\Api\QuotationManagementInterface
     */
    protected function _getQuotationManagement()
    {
        return $this->quotationManagement;
    }


    /**
     * Retrieve quote process model
     *
     * @return \Magestore\Quotation\Model\BackendCart
     */
    protected function _getQuoteProcessModel()
    {
        return $this->backendCart;
    }

    /**
     * @return \Magento\Framework\Registry
     */
    protected function _getRegistry(){
        return $this->registry;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Quotation::quotation');
    }
}
