<?php
/**
 *
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Controller\Quote;

class AddComment extends \Magestore\Quotation\Controller\AbstractAction
{
    /**
     * @var \Magestore\Quotation\Api\QuotationManagementInterface
     */
    protected $quotationManagement;

    /**
     * AddComment constructor.
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
     * Customer quote detail
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $quoteId = $this->getRequest()->getParam('quote_id');
        $comment = $this->getRequest()->getParam('comment');
        if($quoteId){
            $quote = $this->quotationManagement->getQuoteRequest($quoteId);
            if($quote){
                $this->quotationManagement->addCustomterComment($quote, $comment);
            }
        }
        $resultRedirect = $this->createRedirectResult();
        return $resultRedirect->setUrl($this->_url->getUrl('quotation/quote/view', array('quote_id' => $quoteId)));
    }
}
