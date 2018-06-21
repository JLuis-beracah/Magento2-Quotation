<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model;

use Magestore\Quotation\Model\Source\Quote\Status as QuoteStatus;

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
        if (($this->_quote_request === null)) {
            $this->_quote_request = $this->quoteFactory->create();
            $this->_quote_request->setRequestStatus(QuoteStatus::STATUS_ADMIN_PENDING);
            $this->initQuoteData();
        }else{
            $this->initQuoteData();
        }
//        $this->_quote_request->setIgnoreOldQty(true);
//        $this->_quote_request->setIsSuperMode(true);
        return $this->_quote_request;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function initQuoteData(){
        if ($this->getStoreId()) {
            if (!$this->getQuoteId()) {
                $this->_quote_request->setCustomerGroupId($this->groupManagement->getDefaultGroup()->getId());
                $this->_quote_request->setIsActive(false);
                $this->_quote_request->setStoreId($this->getStoreId());

                $this->quoteRepository->save($this->_quote_request);
                $this->setQuoteId($this->_quote_request->getId());
                $this->_quote_request = $this->quoteRepository->get($this->getQuoteId(), [$this->getStoreId()]);
            } else {
                $this->_quote_request = $this->quoteRepository->get($this->getQuoteId(), [$this->getStoreId()]);
                $this->_quote_request->setStoreId($this->getStoreId());
            }

            if ($this->getCustomerId() && $this->getCustomerId() != $this->_quote_request->getCustomerId()) {
                $customer = $this->customerRepository->getById($this->getCustomerId());
                $this->_quote_request->assignCustomer($customer);
            }
        }
        return $this;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function reloadQuote()
    {
        if ($this->getQuoteId()) {
            $this->_quote_request = $this->quoteRepository->get($this->getQuoteId());
            $this->reAssignCustomer();
//            $this->_quote_request->setIgnoreOldQty(true);
//            $this->_quote_request->setIsSuperMode(true);
        }
        return $this;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function reAssignCustomer(){
        $requestCustomerId = $this->getCustomerId();
        $quoteCustomerId = $this->getCustomerId();
        if ($requestCustomerId && $quoteCustomerId && ($requestCustomerId != $quoteCustomerId)) {
            $customer = $this->customerRepository->getById($requestCustomerId);
            $this->_quote_request->assignCustomer($customer);
//            $this->quoteRepository->save($this->_quote_request);
//            $this->reloadQuote();
        }
    }

    /**
     * @return $this
     */
    public function reset(){
        $this->clearStorage();
        $this->_quote_request = null;
        $this->setCustomerId(null);
        $this->setStoreId(null);
        $this->setQuoteId(null);
        $this->setCurrencyId(null);
        return $this;
    }

    /**
     * @return $this
     */
    public function clearQuote(){
        $this->_quote_request = null;
        return $this;
    }

    /**
     * @param $quoteId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isNewAdminQuote($quoteId){
        $isNew = false;
        if ($quoteId) {
            $quote = $this->quoteRepository->get($quoteId);
            $isNew = ($quote && ($quote->getRequestStatus() == QuoteStatus::STATUS_ADMIN_PENDING))?true:false;
        }
        return $isNew;
    }
}
