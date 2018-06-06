<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Quote\Info\Buttons;

/**
 * Class Accept
 * @package Magestore\Quotation\Block\Quote\Info\Buttons
 */
class Accept extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'quote/info/buttons/accept.phtml';

    /**
     * @var \Magestore\Quotation\Api\QuotationManagementInterface
     */
    protected $quotationManagement;

    /**
     * Accept constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement,
        array $data = []
    ) {
        $this->quotationManagement = $quotationManagement;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->getUrl("quotation/quote/checkout", [
            "id" => $this->getRequest()->getParam("quote_id")
        ]);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Accept and checkout');
    }

    /**
     * @return \Magento\Quote\Api\Data\CartInterface|null
     */
    public function getQuote(){
        $quote = null;
        $quoteId = $this->getRequest()->getParam("quote_id");
        if($quoteId){
            $quote = $this->quotationManagement->getQuoteRequest($quoteId);
        }
        return $quote;
    }

    /**
     * @return bool
     */
    public function canCheckout(){
        $canCheckout = false;
        try{
            $quote = $this->getQuote();
            if($quote){
                $result = $this->quotationManagement->canCheckout($quote);
                if($result['error'] === false){
                    $canCheckout = true;
                }
            }
        }catch (\Exception $e){

        }
        return $canCheckout;
    }
}
