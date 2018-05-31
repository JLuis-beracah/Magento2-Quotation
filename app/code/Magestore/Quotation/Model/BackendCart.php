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

}
