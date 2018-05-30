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
     * LoadCustomerQuoteObserver constructor.
     * @param \Magestore\Quotation\Model\Session $quotationSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magestore\Quotation\Model\Session $quotationSession,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->quotationSession = $quotationSession;
        $this->messageManager = $messageManager;
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
    }
}
