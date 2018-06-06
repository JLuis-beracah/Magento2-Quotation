<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\Quotation\Block\Checkout;

/**
 * Class Js
 * @package Magestore\Quotation\Block\Checkout
 */
class Js extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Js constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote(){
        return $this->checkoutSession->getQuote();
    }

    /**
     * @return string
     */
    public function getJsJsonConfig(){
        $quote = $this->getQuote();
        $config = [
            'is_checkout_quotation' => ($quote->getData("quotation_request_id"))?true:false,
            'shipping_address' => [],
            'billing_address' => [],
        ];
        try{
            $shippingAddress = $quote->getShippingAddress();
            $shippingDataObject = $shippingAddress->exportCustomerAddress();
            $config['shipping_address'] = $shippingDataObject->__toArray();
        }catch (\Exception $e){

        }
        try{
            $billingAddress = $quote->getBillingAddress();
            $billingDataObject = $billingAddress->exportCustomerAddress();
            $config['billing_address'] = $billingDataObject->__toArray();
        }catch (\Exception $e){

        }
        return \Zend_Json::encode($config);
    }
}
