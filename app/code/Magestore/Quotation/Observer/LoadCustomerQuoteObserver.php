<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Observer;

use Magento\Framework\Event\ObserverInterface;

class LoadCustomerQuoteObserver implements ObserverInterface
{
    /**
     * @var \Magestore\Quotation\Model\Session
     */
    protected $quotationSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magestore\Quotation\Api\QuotationManagementInterface
     */
    protected $quotationManagement;

    /**
     * LoadCustomerQuoteObserver constructor.
     * @param \Magestore\Quotation\Model\Session $quotationSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement
     */
    public function __construct(
        \Magestore\Quotation\Model\Session $quotationSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement
    ) {
        $this->quotationSession = $quotationSession;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->quotationManagement = $quotationManagement;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $this->quotationSession->loadCustomerQuote();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Load customer quote request error'));
        }
        $validatingRequestId = $this->customerSession->getData("validating_quote_request_id", true);
        if($validatingRequestId){
            try{
                $this->quotationManagement->checkout($validatingRequestId);
            }catch (\Exception $e){
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
    }
}
