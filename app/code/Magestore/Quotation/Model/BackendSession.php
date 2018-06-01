<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model;

/**
 * Class BackendSession
 * @package Magestore\Quotation\Model
 */
class BackendSession extends \Magento\Backend\Model\Session\Quote
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote_request;

    /**
     * Retrieve quote model object
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (($this->_quote_request === null) && $this->getQuoteId()) {
            $this->_quote_request = $this->quoteFactory->create();
            $this->_quote_request = $this->quoteRepository->get($this->getQuoteId());
            $this->_quote_request->setIgnoreOldQty(true);
            $this->_quote_request->setIsSuperMode(true);
        }
        return $this->_quote_request;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function reloadQuote()
    {
        if ($this->getQuoteId()) {
            $this->_quote_request = $this->quoteFactory->create();
            $this->_quote_request = $this->quoteRepository->get($this->getQuoteId());
            $this->_quote_request->setIgnoreOldQty(true);
            $this->_quote_request->setIsSuperMode(true);
        }
        return $this;
    }
}
