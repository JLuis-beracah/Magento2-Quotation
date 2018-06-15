<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Quote;

use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\Address\ToOrderAddress as ConvertQuoteAddressToOrderAddress;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Address\Renderer as OrderAddressRenderer;
use Magestore\Quotation\Model\Source\Quote\Status as QuoteStatus;

/**
 * Class Info
 * @package Magestore\Quotation\Block\Quote
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'quote/info.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;
    
    /**
     * @var OrderAddressRenderer
     */
    protected $addressRenderer;

    /**
     * @var ToOrderAddress
     */
    protected $toOrderAddress;

    /**
     * Info constructor.
     * @param TemplateContext $context
     * @param Registry $registry
     * @param OrderAddressRenderer $addressRenderer
     * @param ConvertQuoteAddressToOrderAddress $toOrderAddress
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        OrderAddressRenderer $addressRenderer,
        ConvertQuoteAddressToOrderAddress $toOrderAddress,
        array $data = []
    ) {
        $this->toOrderAddress = $toOrderAddress;
        $this->addressRenderer = $addressRenderer;
        $this->coreRegistry = $registry;
        $this->_isScopePrivate = true;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Quote # %1', $this->getQuote()->getEntityId()));
    }

    /**
     * Retrieve current quote model instance
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->coreRegistry->registry('current_quote');
    }

    /**
     * Returns string with formatted address
     *
     * @param Address $address
     * @return null|string
     */
    public function getFormattedAddress(QuoteAddress $address)
    {
        $address = $this->toOrderAddress->convert($address);
        return $this->addressRenderer->format($address, 'html');
    }

    /**
     * @return string
     */
    public function getStatusLabel(){
        $quote = $this->getQuote();
        $statusLabel = "";
        if($quote){
            $statusCode = $quote->getRequestStatus();
            $statusList = QuoteStatus::getOptionArray();
            $statusLabel = ($statusCode && isset($statusList[$statusCode]))?$statusList[$statusCode]:"";
        }
        return $statusLabel;
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getShippingMethodInfo(){
        $shippingMethodInfo = __('Shipping & Handling price varies. Please select required quantity and checkout online to see applicable price.');
        $quote = $this->getQuote();
        if($quote){
            if($quote->getShippingAddress()->getShippingMethod() == "admin_shipping_standard"){
                $shippingMethodInfo = $quote->getShippingAddress()->getShippingDescription();
            }
        }
        return $shippingMethodInfo;
    }
}
