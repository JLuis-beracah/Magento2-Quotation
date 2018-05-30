<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Observer;

use Magento\Framework\Event\ObserverInterface;

class UnsetAllObserver implements ObserverInterface
{
    /**
     * @var \Magestore\Quotation\Model\Session
     */
    protected $quotationSession;

    /**
     * UnsetAllObserver constructor.
     * @param \Magestore\Quotation\Model\Session $quotationSession
     */
    public function __construct(\Magestore\Quotation\Model\Session $quotationSession)
    {
        $this->quotationSession = $quotationSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @codeCoverageIgnore
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->quotationSession->clearQuote()->clearStorage();
    }
}
