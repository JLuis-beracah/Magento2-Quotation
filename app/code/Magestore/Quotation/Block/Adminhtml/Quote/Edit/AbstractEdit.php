<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Adminhtml\Quote\Edit;

/**
 * Class AbstractEdit
 * @package Magestore\Quotation\Block\Adminhtml\Quote\Edit
 */
abstract class AbstractEdit extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magestore\Quotation\Model\BackendSession
     */
    protected $quoteSession;

    /**
     * @var \Magestore\Quotation\Model\BackendCart
     */
    protected $quoteCart;

    /**
     * @var \Magestore\Quotation\Api\QuotationManagementInterface
     */
    protected $quotationManagement;

    /**
     * AbstractEdit constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Registry $registry
     * @param \Magestore\Quotation\Model\BackendSession $quoteSession
     * @param \Magestore\Quotation\Model\BackendCart $quoteCart
     * @param \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Registry $registry,
        \Magestore\Quotation\Model\BackendSession $quoteSession,
        \Magestore\Quotation\Model\BackendCart $quoteCart,
        \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement,
        array $data = []
    ) {
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
        $this->registry = $registry;
        $this->quoteSession = $quoteSession;
        $this->quoteCart = $quoteCart;
        $this->quotationManagement = $quotationManagement;
    }

    /**
     * @return \Magestore\Quotation\Model\BackendCart
     */
    public function getEditQuoteModel()
    {
        return $this->quoteCart;
    }

    /**
     * @return \Magestore\Quotation\Model\BackendSession
     */
    protected function _getSession()
    {
        return $this->quoteSession;
    }


    /**
     * @return \Magento\Quote\Model\Quote|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuote()
    {
        if ($this->hasQuote()) {
            return $this->getData('quote');
        }
        if ($this->registry->registry('current_quote_request')) {
            return $this->registry->registry('current_quote_request');
        }
        if ($this->registry->registry('quote')) {
            return $this->registry->registry('quote');
        }
        throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t get the quote instance right now.'));
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function canEdit(){
        return $this->quotationManagement->canEdit($this->getQuote());
    }
}
