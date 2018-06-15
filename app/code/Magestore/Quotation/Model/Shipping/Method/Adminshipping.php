<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Quotation\Model\Shipping\Method;

use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * Class Adminshipping
 * @package Magestore\Quotation\Model\Shipping\Method
 */
class Adminshipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements \Magento\Shipping\Model\Carrier\AbstractCarrierInterface
{

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = 'admin_shipping';

    /**
     * Method's code
     *
     * @var string
     */
    protected $_method_code = 'standard';

    /**
     * Request object
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request = '';

    /**
     * @var \Magestore\Quotation\Api\QuotationManagementInterface
     */
    protected $quotationManagement;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Magestore\Quotation\Model\BackendCart
     */
    protected $backendQuotationCart;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * Adminshipping constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement
     * @param \Magento\Checkout\Model\Cart $checkoutCart
     * @param \Magestore\Quotation\Model\BackendCart $backendQuotationCart
     * @param \Magento\Framework\App\State $appState
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magestore\Quotation\Model\BackendCart $backendQuotationCart,
        \Magento\Framework\App\State $appState,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_request = $request;
        $this->quotationManagement = $quotationManagement;
        $this->checkoutCart = $checkoutCart;
        $this->backendQuotationCart = $backendQuotationCart;
        $this->appState = $appState;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @return string
     */
    public function getShippingMethodCode(){
        return $this->_code."_".$this->_method_code;
    }

    /**
     *
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result
     */
    public function collectRates(RateRequest $request)
    {
        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();
        $result->append($this->_getRate());
        return $result;
    }

    /**
     *
     * @return \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected function _getRate()
    {
        $rate = $this->_rateMethodFactory->create();
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod($this->_method_code);
        $rate->setMethodTitle($this->getConfigData('name'));
        $rate->setPrice($this->getConfigData('price'));
        $rate->setCost($this->getConfigData('price'));

        $quotationRequest = $this->getQuotationRequest();
        if($quotationRequest){
            $adminShippingAmount = $quotationRequest->getAdminShippingAmount();
            $adminShippingDescription = $quotationRequest->getAdminShippingDescription();
            if($adminShippingAmount !== null){
                $rate->setPrice($adminShippingAmount);
                $rate->setCost($adminShippingAmount);
            }
            if($adminShippingDescription){
                $rate->setMethodTitle($adminShippingDescription);
            }
        }
        return $rate;
    }

    /**
     * @return bool|string
     */
    public function getCurrentArea(){
        try{
            $areaCode = $this->appState->getAreaCode();
        }catch (\Magento\Framework\Exception\LocalizedException $e){
            $areaCode = false;
        }
        return $areaCode;
    }

    /**
     * @return \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote|null
     */
    public function getQuotationRequest(){
        $areaCode = $this->getCurrentArea();
        if($areaCode  === 'adminhtml'){
            return $this->backendQuotationCart->getQuote();
        }
        $quote = $this->checkoutCart->getQuote();
        $quotationRequestId = $quote->getQuotationRequestId();
        if($quotationRequestId){
            return $this->quotationManagement->getQuoteRequest($quotationRequestId);
        }
        return null;
    }

    /**
     * @return bool
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * Enable method
     * @return bool
     */
    public function isActive()
    {
        $active = $this->getConfigData('active');
        if ($active == 1 || $active == 'true'){
            $areaCode = $this->getCurrentArea();
            if($areaCode  === 'adminhtml'){
                return ($this->_request->getRouteName() == "quotation")?true:false;
            }
            if($areaCode  === 'frontend'){
                $quote = $this->checkoutCart->getQuote();
                $quotationRequestId = $quote->getQuotationRequestId();
                $shipMethod = $quote->getShippingAddress()->getShippingMethod();
                if($quotationRequestId && ($shipMethod == $this->getShippingMethodCode())){
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Enable method for Web POS only
     * @return bool
     */
    public function checkAvailableShipCountries(\Magento\Framework\DataObject $request)
    {
        return true;
    }

}
