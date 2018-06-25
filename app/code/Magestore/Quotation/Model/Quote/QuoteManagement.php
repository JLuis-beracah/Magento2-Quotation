<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model\Quote;

/**
 * Class QuotationManagement
 * @package Magestore\Quotation\Model
 */
class QuoteManagement extends \Magento\Quote\Model\QuoteManagement
{

    /**
     * @param $quote
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */
    public function updateCustomerAddress($quote){
        if (!$quote->getCustomerIsGuest()) {
            if ($quote->getCustomerId()) {
                $this->_prepareCustomerQuote($quote);
                $this->customerManagement->validateAddresses($quote);
            }
            $this->customerManagement->populateCustomerInfo($quote);
            $this->quoteRepository->save($quote);
        }
        return $this;
    }
}
