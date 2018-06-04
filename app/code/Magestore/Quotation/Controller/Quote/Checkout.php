<?php
/**
 *
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Controller\Quote;

use Magento\Directory\Model\RegionFactory;

class Checkout extends \Magestore\Quotation\Controller\AbstractAction
{
    /**
     * @var \Magestore\Quotation\Api\QuotationManagementInterface
     */
    protected $quotationManagement;

    /**
     * Checkout constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magestore\Quotation\Helper\Data $helper
     * @param \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magestore\Quotation\Helper\Data $helper,
        \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement
    ){
        parent::__construct($context, $helper);
        $this->quotationManagement = $quotationManagement;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $quoteId = $this->getRequest()->getParam('id');
        try{
            $result = $this->quotationManagement->checkout($quoteId);
            if(!empty($result['redirect_url'])){
                $url = $result['redirect_url'];
            }else{
                $url = $this->_url->getUrl('checkout');
            }
        }catch (\Exception $e){
            $this->messageManager->addErrorMessage($e->getMessage());
            $url = $this->_url->getUrl();
        }
        $resultRedirect = $this->createRedirectResult();
        return $resultRedirect->setUrl($url);
    }
}
