<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Adminhtml\Quote;

use Magestore\Quotation\Model\Source\Quote\Status as QuoteStatus;

/**
 * Class Address
 * @package Magestore\Quotation\Block\Adminhtml\Quote
 */
class Address extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magestore\Quotation\Api\QuotationManagementInterface|null
     */
    protected $quotationManagement;

    /**
     * Address constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->quotationManagement = $quotationManagement;
        parent::__construct($context, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_quote';
        $this->_mode = 'address';
        $this->_blockGroup = 'Magestore_Quotation';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Quote Address'));
        $this->buttonList->remove('delete');
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $address = $this->_coreRegistry->registry('current_quote_address');
        $quoteId = $address->getQuote()->getEntityId();
        if ($address->getAddressType() == 'shipping') {
            $type = __('Shipping');
        } else {
            $type = __('Billing');
        }
        return __('Edit Quote %1 %2 Address', $quoteId, $type);
    }

    /**
     * Back button url getter
     *
     * @return string
     */
    public function getBackUrl()
    {
        $address = $this->_coreRegistry->registry('current_quote_address');
        $quoteId = $address->getQuoteId();
        $quote = $this->quotationManagement->getQuoteRequest($quoteId);
        $url = $this->getUrl('quotation/quote/edit');
        if($quote && ($quote->getRequestStatus() != QuoteStatus::STATUS_ADMIN_PENDING)){
            $url = $this->getUrl('quotation/quote/edit', ['id' => $address ? $address->getQuoteId() : null]);
        }
        return $url;
    }
}
