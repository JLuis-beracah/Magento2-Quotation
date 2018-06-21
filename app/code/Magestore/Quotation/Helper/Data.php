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
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Escaper $escaper
    )
    {
        parent::__construct($context);
        $this->currencyFactory = $currencyFactory;
        $this->timezone = $timezone;
        $this->escaper = $escaper;
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
     * @param $path
     * @param null $storeCode
     * @return mixed
     */
    public function getStoreConfig($path, $storeCode = null){
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeCode);
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

    /**
     * Retrieve formatting date
     *
     * @param null|string|\DateTimeInterface $date
     * @param int $format
     * @param bool $showTime
     * @param null|string $timezone
     * @return string
     */
    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    ) {
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);
        return $this->timezone->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }

    /**
     * @param $string
     * @return array|string
     */
    public function escapeHtml($string){
        return $this->escaper->escapeHtml($string);
    }
}