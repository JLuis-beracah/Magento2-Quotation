<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magestore\Quotation\Model;

use Magento\Framework\App\ObjectManager;

/**
 * Class BackendCart
 * @package Magestore\Quotation\Model
 */
class BackendCart extends \Magento\Sales\Model\AdminOrder\Create
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote_request;

    /**
     * @var \Magestore\Quotation\Model\GeneralSession
     */
    protected $_general_session;

    /**
     * @return \Magestore\Quotation\Model\BackendSession
     */
    public function getSession()
    {
        if( !$this->_session ||
            !($this->_session instanceof \Magestore\Quotation\Model\BackendSession)
        ) {
            $this->_session = ObjectManager::getInstance()->get(\Magestore\Quotation\Model\BackendSession::class);
        }
        return $this->_session;
    }

    /**
     * @return \Magestore\Quotation\Model\GeneralSession
     */
    public function getGeneralSession()
    {
        if( !$this->_general_session ||
            !($this->_general_session instanceof \Magestore\Quotation\Model\GeneralSession)
        ) {
            $this->_general_session = ObjectManager::getInstance()->get(\Magestore\Quotation\Model\GeneralSession::class);
        }
        return $this->_general_session;
    }

    /**
     * Retrieve quote object model
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (!$this->_quote_request) {
            $this->_quote_request = $this->getSession()->getQuote();
        }

        return $this->_quote_request;
    }

    /**
     * Set quote object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     */
    public function setQuote(\Magento\Quote\Model\Quote $quote)
    {
        $this->_quote_request = $quote;
        return $this;
    }


    /**
     * Update quantity of order quote items
     *
     * @param array $items
     * @return $this
     * @throws \Exception|\Magento\Framework\Exception\LocalizedException
     */
    public function updateQuoteItems($items)
    {
        if (!is_array($items)) {
            return $this;
        }
        parent::updateQuoteItems($items);
        try {
            foreach ($items as $itemId => $info) {
                if (!empty($info['configured'])) {
                    $item = $this->getQuote()->updateItem($itemId, $this->objectFactory->create($info));
                } else {
                    $item = $this->getQuote()->getItemById($itemId);
                    if (!$item) {
                        continue;
                    }
                }
                if ($item && empty($info['custom_price'])) {
                    $item->setOriginalCustomPrice(null);
                    $item->setCustomPrice(null);
                }
                if($item && isset($info['admin_discount_percentage'])){
                    $percentage = intval($info['admin_discount_percentage']);
                    $percentage = min(100, $percentage);
                    $percentage = max(0, $percentage);
                    $item->setAdminDiscountPercentage($percentage);
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->recollectCart();
            throw $e;
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        $this->recollectCart();

        return $this;
    }

    /**
     * @param array $data
     * @return $this|\Magento\Sales\Model\AdminOrder\Create
     */
    public function importPostData($data)
    {
        parent::importPostData($data);
        if($this->getShippingMethod() && $this->getShippingMethod() == 'admin_shipping_standard') {
            if (isset($data['admin_shipping_amount'])) {
                $shippingPrice = $this->_parseAmount($data['admin_shipping_amount']);
                $this->getQuote()->setAdminShippingAmount($shippingPrice);
            }
            if (isset($data['admin_shipping_description'])) {
                $this->getQuote()->setAdminShippingDescription($data['admin_shipping_description']);
            }
            $this->collectShippingRates();
        }

        return $this;
    }

    /**
     * @param $amount
     * @return float|int|null
     */
    public function _parseAmount($amount){
        if($amount === ""){
            return null;
        }
        $amount = floatval($amount);
        $amount = $amount > 0 ? $amount : 0;
        return $amount;
    }
}
