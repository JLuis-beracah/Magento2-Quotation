<?php
namespace Magestore\Quotation\Observer;
use Magento\Framework\Event\ObserverInterface;

class QuoteSubmitSuccess implements ObserverInterface
{
    /**
     * @var \Magestore\Quotation\Api\QuotationManagementInterface
     */
    protected $quotationManagement;

    /**
     * QuoteSubmitSuccess constructor.
     * @param \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement
     */
    public function __construct(
        \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement
    ) {
        $this->quotationManagement = $quotationManagement;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getData('order');
        $this->quotationManagement->order($order);
        return $this;
    }
}