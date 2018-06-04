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
    const ERROR_NOT_LOGIN = "customer_not_login";
    const ERROR_INVALID_CUSTOMER = "invalid_customer";
    const ERROR_REQUEST_EXPIRED = "request_expired";
    const ERROR_REQUEST_IS_NOT_PROCESSED = "request_is_not_processed";

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
     * @return array
     */
    public function canCheckout(\Magento\Quote\Api\Data\CartInterface $quote);
}
