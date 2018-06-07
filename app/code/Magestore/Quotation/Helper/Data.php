<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\Quotation\Helper;

/**
 * Class Data
 * @package Magestore\Quotation\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    )
    {
        parent::__construct($context);
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * @param $quote
     * @param $amount
     * @return mixed
     */
    public function formatQuotePrice($quote, $amount){
        $quoteCurrency =  $this->currencyFactory->create();
        $quoteCurrency->load($quote->getQuoteCurrencyCode());
        return $quoteCurrency->formatPrecision($amount, 2, [], true, false);
    }

    /**
     * @param $quote
     * @param $amount
     * @return mixed
     */
    public function formatQuoteBasePrice($quote, $amount){
        $quoteCurrency =  $this->currencyFactory->create();
        $quoteCurrency->load($quote->getBaseCurrencyCode());
        return $quoteCurrency->formatPrecision($amount, 2, [], true, false);
    }


    /**
     *
     * @param string $path
     * @return string
     */
    public function getStoreConfig($path){
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve url
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route, $params = [])
    {
        return $this->_getUrl($route, $params);
    }
}