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
}
