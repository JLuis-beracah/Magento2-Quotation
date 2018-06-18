<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Controller\Adminhtml\Quote;

/**
 * Class Edit
 * @package Magestore\Quotation\Controller\Adminhtml\Quote
 */
class Edit extends \Magestore\Quotation\Controller\Adminhtml\Quote\QuoteAbstract
{

    /**
     * @return $this|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->_initSession();
        $quote = $this->_getQuote();
        $resultPage = $this->createPageResult();
        $resultPage->getConfig()->getTitle()->prepend(($quote->getId())?__('Quote #%1', $quote->getId()):__('New Quote'));
        return $resultPage;
    }

}