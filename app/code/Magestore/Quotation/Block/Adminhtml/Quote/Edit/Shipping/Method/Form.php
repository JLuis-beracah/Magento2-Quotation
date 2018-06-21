<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Adminhtml\Quote\Edit\Shipping\Method;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Form
 * @package Magestore\Quotation\Block\Adminhtml\Quote\Edit\Shipping\Method
 */
class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magestore\Quotation\Api\QuotationManagementInterface
     */
    protected $quotationManagement;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Registry $registry
     * @param \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Registry $registry,
        \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement,
        array $data = []
    ) {
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $taxData, $data);
        $this->registry = $registry;
        $this->quotationManagement = $quotationManagement;
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quotation_quote_edit_shipping_method_form');
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
     * @return string
     */
    public function getAdminShippingMethodCode(){
        return "admin_shipping_standard";
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function canEdit(){
        return $this->quotationManagement->canEdit($this->getQuote());
    }
}
