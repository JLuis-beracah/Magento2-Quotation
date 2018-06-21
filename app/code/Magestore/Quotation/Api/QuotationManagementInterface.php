<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Api;

/**
 * Interface QuotationManagementInterface
 * @package Magento\Quotation\Api
 */
interface QuotationManagementInterface
{
    const ERROR_NOT_LOGIN = 99;
    const ERROR_INVALID_CUSTOMER = 98;
    const ERROR_REQUEST_EXPIRED = 97;
    const ERROR_REQUEST_IS_NOT_PROCESSED = 96;
    const ERROR_REQUEST_HAS_BEEN_ORDERED = 95;
    const ERROR_REQUEST_HAS_BEEN_DECLINED = 94;

    /**
     * @param int $quoteId
     * @return \Magento\Quote\Api\Data\CartInterface|null
     */
    public function getQuoteRequest($quoteId);

    /**
     * @param int $customerId
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function getActiveForCustomer($customerId);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function start(\Magento\Quote\Api\Data\CartInterface $quote);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function submit(\Magento\Quote\Api\Data\CartInterface $quote);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function process(\Magento\Quote\Api\Data\CartInterface $quote);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function send(\Magento\Quote\Api\Data\CartInterface $quote);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function decline(\Magento\Quote\Api\Data\CartInterface $quote);

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magestore\Quotation\Api\QuotationManagementInterface
     */
    public function order(\Magento\Sales\Api\Data\OrderInterface $order);


    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return bool|\Magento\Quote\Api\Data\CartInterface
     */
    public function getOrderQuotation(\Magento\Sales\Api\Data\OrderInterface $order);

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return bool
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function validateBeforePlaceOrder(\Magento\Sales\Api\Data\OrderInterface $order);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    public function canDecline(\Magento\Quote\Api\Data\CartInterface $quote);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    public function canSend(\Magento\Quote\Api\Data\CartInterface $quote);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    public function canEdit(\Magento\Quote\Api\Data\CartInterface $quote);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return array
     */
    public function getChangeAbleStatus(\Magento\Quote\Api\Data\CartInterface $quote);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param string $expirationDate
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function setExpirationDate(\Magento\Quote\Api\Data\CartInterface $quote, $expirationDate = "");

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    public function validateExpirationDate(\Magento\Quote\Api\Data\CartInterface $quote);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param int $quoteStatus
     * @param null $itemsStatus
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function updateStatus(\Magento\Quote\Api\Data\CartInterface $quote, $quoteStatus, $itemsStatus = null);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    public function isExpired(\Magento\Quote\Api\Data\CartInterface $quote);

    /**
     * @return $this
     */
    public function validateAllRequest();

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return $this
     */
    public function sendEmail(\Magento\Quote\Api\Data\CartInterface $quote);

    /**
     * @param $quoteId
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function checkout($quoteId);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param bool $removeExistedItems
     * @return $this
     */
    public function moveToShoppingCart(\Magento\Quote\Api\Data\CartInterface $quote, $removeExistedItems = true);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return array
     */
    public function canView(\Magento\Quote\Api\Data\CartInterface $quote);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function canOrder(\Magento\Quote\Api\Data\CartInterface $quote);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return array
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function canCheckout(\Magento\Quote\Api\Data\CartInterface $quote);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param string $comment
     * @param int $status
     * @param int $visible
     * @param int $notify
     * @return \Magestore\Quotation\Api\Data\QuoteCommentHistoryInterface
     */
    public function addAdminComment(\Magento\Quote\Api\Data\CartInterface $quote, $comment, $status, $visible, $notify = 0);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param $comment
     * @return QuoteCommentHistoryInterface
     */
    public function addCustomterComment(\Magento\Quote\Api\Data\CartInterface $quote,  $comment);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magestore\Quotation\Model\ResourceModel\Quote\Comment\History\Collection
     */
    public function getCommentHistory(\Magento\Quote\Api\Data\CartInterface $quote);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param int $salesrep
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function setSalesrep(\Magento\Quote\Api\Data\CartInterface $quote, $salesrep = 0);

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param string $recipientEmails
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function setRecipientEmails(\Magento\Quote\Api\Data\CartInterface $quote, $recipientEmails = 0);
}
