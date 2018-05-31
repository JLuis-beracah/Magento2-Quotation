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
     * AbstractAction constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magestore\Quotation\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magestore\Quotation\Helper\Data $helper
    ){
        parent::__construct($context);
        $this->helper = $helper;
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
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Quotation::quotation');
    }
}
