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
            if($this->getQuoteId()){
                $this->_quote_request = $this->quoteFactory->create();
                $this->_quote_request = $this->quoteRepository->get($this->getQuoteId());
                $this->_quote_request->setIgnoreOldQty(true);
                $this->_quote_request->setIsSuperMode(true);
            }else{
                $this->_quote_request = $this->quoteFactory->create();
                if ($this->getStoreId()) {
                    $this->_quote_request->setCustomerGroupId($this->groupManagement->getDefaultGroup()->getId());
                    $this->_quote_request->setIsActive(false);
                    $this->_quote_request->setStoreId($this->getStoreId());
                    $this->_quote_request->setRequestStatus(QuoteStatus::STATUS_ADMIN_PENDING);
                    $this->quoteRepository->save($this->_quote_request);
                    $this->setQuoteId($this->_quote_request->getId());
                    $this->_quote_request = $this->quoteRepository->get($this->getQuoteId(), [$this->getStoreId()]);

                    if ($this->getCustomerId() && $this->getCustomerId() != $this->_quote_request->getCustomerId()) {
                        $customer = $this->customerRepository->getById($this->getCustomerId());
                        $this->_quote_request->assignCustomer($customer);
                        $this->quoteRepository->save($this->_quote_request);
                    }
                }
                $this->_quote_request->setIgnoreOldQty(true);
                $this->_quote_request->setIsSuperMode(true);
            }
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

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function reAssignCustomer(){
        if ($this->getCustomerId() && $this->getCustomerId() != $this->getQuote()->getCustomerId()) {
            $customer = $this->customerRepository->getById($this->getCustomerId());
            $this->_quote_request->assignCustomer($customer);
            $this->quoteRepository->save($this->_quote_request);
            $this->reloadQuote();
        }
    }

    /**
     * @return $this
     */
    public function reset(){
        $this->clearStorage();
        $this->_quote_request = null;
        return $this;
    }
}
