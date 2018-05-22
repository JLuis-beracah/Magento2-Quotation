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
}