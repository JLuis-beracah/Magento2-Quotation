<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model;

use Magestore\Quotation\Model\Source\Quote\Status as QuoteStatus;
use Magestore\Webpos\Model\Cart\Data\Quote;

/**
 * Class QuotationManagement
 * @package Magestore\Quotation\Model
 */
class QuotationManagement implements \Magestore\Quotation\Api\QuotationManagementInterface
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magestore\Quotation\Model\Quote\Email\Sender
     */
    protected $quoteSender;

    /**
     * @var \Magestore\Quotation\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Checkout\Model\Type\Onepage
     */
    protected $onepageCheckout;

    /**
     * QuotationManagement constructor.
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $collectionFactory
     * @param Quote\Email\Sender $quoteSender
     * @param \Magestore\Quotation\Helper\Data $helper
     * @param \Magento\Checkout\Model\Cart $checkoutCart
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Checkout\Model\Type\Onepage $onepageCheckout
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $collectionFactory,
        \Magestore\Quotation\Model\Quote\Email\Sender $quoteSender,
        \Magestore\Quotation\Helper\Data $helper,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Type\Onepage $onepageCheckout
    ) {
        $this->eventManager = $eventManager;
        $this->quoteRepository = $quoteRepository;
        $this->collectionFactory = $collectionFactory;
        $this->quoteSender = $quoteSender;
        $this->helper = $helper;
        $this->checkoutCart = $checkoutCart;
        $this->logger = $logger;
        $this->onepageCheckout = $onepageCheckout;
    }

    /**
     * @param int $quoteId
     * @return \Magento\Quote\Api\Data\CartInterface|null
     */
    public function getQuoteRequest($quoteId){
        try{
            $quote = $this->quoteRepository->get($quoteId);
            if($quote->getRequestStatus() == QuoteStatus::STATUS_NONE){
                $quote = null;
            }
        }catch (\Exception $e){
            $quote = null;
        }
        return $quote;
    }

    /**
     * @param int $customerId
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function getActiveForCustomer($customerId){
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);
        $collection->addFieldToFilter('request_status', QuoteStatus::STATUS_PENDING);
        $quote = $collection->getFirstItem();
        if(!$quote || !$quote->getId() || !$customerId){
            throw \Magento\Framework\Exception\NoSuchEntityException::singleField("customer_id", $customerId);
        }
        return $quote;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function start(\Magento\Quote\Api\Data\CartInterface $quote){
        $quote->setIsActive(false);
        $this->updateStatus($quote, QuoteStatus::STATUS_PENDING);
        return $quote;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function submit(\Magento\Quote\Api\Data\CartInterface $quote){
        $quote->setIsActive(false);
        $this->updateStatus($quote, QuoteStatus::STATUS_NEW, QuoteStatus::STATUS_PROCESSING);
        return $quote;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function process(\Magento\Quote\Api\Data\CartInterface $quote){
        $quote->setIsActive(false);
        $this->updateStatus($quote, QuoteStatus::STATUS_PROCESSING);
        return $quote;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function send(\Magento\Quote\Api\Data\CartInterface $quote){
        if($this->canSend($quote)){
            $quote->setIsActive(false);
            if(!$this->isExpired($quote)){
                $this->updateStatus($quote, QuoteStatus::STATUS_PROCESSED, QuoteStatus::STATUS_PROCESSED);
                $this->sendEmail($quote);
            }
        }else{
            throw new \Magento\Framework\Exception\ValidatorException(__('This quote request cannot be sent'));
        }
        return $quote;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function decline(\Magento\Quote\Api\Data\CartInterface $quote){
        if($this->canDecline($quote)){
            $quote->setIsActive(false);
            $this->updateStatus($quote, QuoteStatus::STATUS_DECLINED, QuoteStatus::STATUS_DECLINED);
        }else{
            throw new \Magento\Framework\Exception\ValidatorException(__('This quote request cannot be declined'));
        }
        return $quote;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return $this
     */
    public function order(\Magento\Sales\Api\Data\OrderInterface $order){
        $quote = $this->getOrderQuotation($order);
        if($quote && $quote->getId()){
            $quote->setIsActive(false);
            $quote->setData("request_ordered_id", $order->getId());
            $quote->setData("request_ordered_increment_id", $order->getIncrementId());
            $this->updateStatus($quote, QuoteStatus::STATUS_ORDERED, QuoteStatus::STATUS_ORDERED);
        }
        return $this;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return bool|\Magento\Quote\Api\Data\CartInterface
     */
    public function getOrderQuotation(\Magento\Sales\Api\Data\OrderInterface $order){
        $quote = false;
        $quoteRequestId = $order->getData("quotation_request_id");
        if($quoteRequestId){
            try{
                $quote = $this->quoteRepository->get($quoteRequestId);
            }catch (\Exception $e){
                $this->logger->critical($e);
            }
        }
        return $quote;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return bool
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function validateBeforePlaceOrder(\Magento\Sales\Api\Data\OrderInterface $order){
        $canOrder = true;
        $quote = $this->getOrderQuotation($order);
        if($quote && $quote->getId()){
            $this->canOrder($quote);
        }
        return $canOrder;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    public function canDecline(\Magento\Quote\Api\Data\CartInterface $quote){
        $requestStatus = $quote->getData("request_status");
        return (
        ($requestStatus == QuoteStatus::STATUS_DECLINED) ||
        ($requestStatus == QuoteStatus::STATUS_EXPIRED) ||
        ($requestStatus == QuoteStatus::STATUS_ORDERED)
        )?false:true;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    public function canSend(\Magento\Quote\Api\Data\CartInterface $quote){
        $requestStatus = $quote->getData("request_status");
        return (
        ($requestStatus == QuoteStatus::STATUS_DECLINED) ||
        ($requestStatus == QuoteStatus::STATUS_EXPIRED) ||
        ($requestStatus == QuoteStatus::STATUS_ORDERED)
        )?false:true;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    public function canEdit(\Magento\Quote\Api\Data\CartInterface $quote){
        $requestStatus = $quote->getData("request_status");
        return (
        ($requestStatus == QuoteStatus::STATUS_DECLINED) ||
        ($requestStatus == QuoteStatus::STATUS_EXPIRED) ||
        ($requestStatus == QuoteStatus::STATUS_PROCESSED) ||
        ($requestStatus == QuoteStatus::STATUS_ORDERED)
        )?false:true;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param string $expirationDate
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function setExpirationDate(\Magento\Quote\Api\Data\CartInterface $quote, $expirationDate = ""){
        $quote->setData("expiration_date", $expirationDate);
        if($quote->getId()){
            $this->quoteRepository->save($quote);
        }
        return $quote;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    public function validateExpirationDate(\Magento\Quote\Api\Data\CartInterface $quote){
        $expirationDate = $quote->getData("expiration_date");
        if($expirationDate){
            $now = new \DateTime();
            $dateEnd = new \DateTime($expirationDate);
            $diff = $now->diff($dateEnd);
            if($diff->invert > 0){
                $this->updateStatus($quote, QuoteStatus::STATUS_EXPIRED, QuoteStatus::STATUS_EXPIRED);
                return false;
            }
        }
        return true;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param int $quoteStatus
     * @param null $itemsStatus
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function updateStatus(\Magento\Quote\Api\Data\CartInterface $quote, $quoteStatus, $itemsStatus = null){
        $quote->setData("request_status", $quoteStatus);
        if($itemsStatus){
            $items = $quote->getAllItems();
            if(!empty($items)){
                foreach ($items as $item){
                    $item->setData("request_status", $itemsStatus);
                }
            }
        }
        if($quote->getId()){
            $this->quoteRepository->save($quote);
        }
        return $quote;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    public function isExpired(\Magento\Quote\Api\Data\CartInterface $quote){
        if($quote->getRequestStatus() == QuoteStatus::STATUS_PROCESSED){
            $this->validateExpirationDate($quote);
        }
        if($quote->getRequestStatus() == QuoteStatus::STATUS_EXPIRED){
            return true;
        }
        return false;
    }

    /**
     * @return $this
     */
    public function validateAllRequest(){
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('request_status', QuoteStatus::STATUS_PROCESSED);
        if($collection->getSize() > 0){
            foreach ($collection as $quote){
                $this->isExpired($quote);
            }
        }
        return $this;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return $this
     */
    public function sendEmail(\Magento\Quote\Api\Data\CartInterface $quote){
        $sendEmailResult = $this->quoteSender->send($quote);
        $quote->setEmailSent($sendEmailResult);
        if($quote->getId()){
            $this->quoteRepository->save($quote);
        }
        return $this;
    }

    /**
     * @param $quoteId
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function checkout($quoteId){
        $result = [];
        $error = false;
        if($quoteId){
            $quote = $this->quoteRepository->get($quoteId);
            if($quote && $quote->getId()){
                $canCheckout = $this->canCheckout($quote);
                if($canCheckout['error'] === false){
                    $this->moveToShoppingCart($quote);
                }else{
                    $errorCode = $canCheckout['error_code'];
                    if($errorCode == self::ERROR_NOT_LOGIN){
                        $this->checkoutCart->getCustomerSession()->setData("validating_quote_request_id", $quoteId);
                        $result['redirect_url'] = $this->helper->getUrl('customer/account/login');
                    }else{
                        $error = $canCheckout['error_message'];
                    }
                }
            }else{
                $error = __("This quote request does not exist!");
            }
        }else{
            $error = __("This quote request does not exist!");
        }
        if($error !== false){
            throw new \Magento\Framework\Exception\ValidatorException($error);
        }
        return $result;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param bool $removeExistedItems
     * @return $this
     */
    public function moveToShoppingCart(\Magento\Quote\Api\Data\CartInterface $quote, $removeExistedItems = true){
        $checkoutSession = $this->checkoutCart->getCheckoutSession();
        $customerSession = $this->checkoutCart->getCustomerSession();
        $shoppingCart = $this->checkoutCart->getQuote();
        $isLoggedIn = $customerSession->isLoggedIn();
        if($removeExistedItems){
            if($isLoggedIn){
                $shoppingCart->setIsActive(false);
                if($shoppingCart->getId()){
                    $this->quoteRepository->save($shoppingCart);
                }
            }
            $checkoutSession->clearQuote();
            $shoppingCart = $checkoutSession->getQuote();
            $this->updateStatus($shoppingCart, QuoteStatus::STATUS_NONE, QuoteStatus::STATUS_NONE);
            $this->checkoutCart->setQuote($shoppingCart);

        }
        $shoppingCart->setIsMultiShipping(false);
        $shoppingCart->setData("quotation_request_id", $quote->getId());
        $shoppingCart->merge($quote);

        if(!$isLoggedIn){
            $shoppingCart->setCustomerEmail($quote->getCustomerEmail());
        }

        $billingAddress = $quote->getBillingAddress();
        if($billingAddress){
            $newBillingAddress = clone $billingAddress;
            $newBillingAddress->unsetData("customer_address_id");
            $newBillingAddress->unsetData("address_id");
            $newBillingAddress->unsetData("quote_id");
            $newBillingAddress->unsetData("created_at");
            $newBillingAddress->unsetData("updated_at");
            $shoppingCart->getBillingAddress()->addData($newBillingAddress->getData());
        }

        $shippingAddress = $quote->getShippingAddress();
        if($shippingAddress){
            $newShippingAddress = clone $shippingAddress;
            $newShippingAddress->unsetData("customer_address_id");
            $newShippingAddress->unsetData("address_id");
            $newShippingAddress->unsetData("quote_id");
            $newShippingAddress->unsetData("created_at");
            $newShippingAddress->unsetData("updated_at");
            $shoppingCart->getShippingAddress()->addData($newShippingAddress->getData());
            $shoppingCart->getShippingAddress()
                ->setCollectShippingRates(true)
                ->collectShippingRates();
        }
        $this->checkoutCart->save();
        $this->onepageCheckout->setQuote($this->checkoutCart->getQuote());
        return $this;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return array
     */
    public function canView(\Magento\Quote\Api\Data\CartInterface $quote){
        $result = [
            'error' => false,
            'error_code' => '',
            'error_message' => ''
        ];
        if(!$quote->getCustomerIsGuest()){
            $isLoggedIn = $this->checkoutCart->getCustomerSession()->isLoggedIn();
            if($isLoggedIn){
                $customerId = $this->checkoutCart->getCustomerSession()->getCustomerId();
                if($customerId != $quote->getCustomerId()){
                    $result['error'] = true;
                    $result['error_code'] = self::ERROR_INVALID_CUSTOMER;
                    $result['error_message'] = __("You don't have perrmission to access this request");
                }
            }else{
                $result['error'] = true;
                $result['error_code'] = self::ERROR_NOT_LOGIN;
                $result['error_message'] = __("You don't have perrmission to access this request");
            }
        }
        return $result;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function canOrder(\Magento\Quote\Api\Data\CartInterface $quote){
        $canOrder = [
            'error' => false,
            'error_code' => '',
            'error_message' => ''
        ];
        $status = $quote->getRequestStatus();
        switch ($status){
            case QuoteStatus::STATUS_PROCESSED:
                $isExpired = $this->isExpired($quote);
                if($isExpired){
                    $canOrder['error'] = true;
                    $canOrder['error_code'] = self::ERROR_REQUEST_EXPIRED;
                    $canOrder['error_message'] = __("This quotation has been expired, please submit a new quote request");
                }
                break;

            case QuoteStatus::STATUS_EXPIRED:
                $canOrder['error'] = true;
                $canOrder['error_code'] = self::ERROR_REQUEST_EXPIRED;
                $canOrder['error_message'] = __("This quotation has been expired, please submit a new quote request");
                break;

            case QuoteStatus::STATUS_DECLINED:
                $canOrder['error'] = true;
                $canOrder['error_code'] = self::ERROR_REQUEST_HAS_BEEN_DECLINED;
                $canOrder['error_message'] = __("This quotation has been declined, please submit a new quote request");
                break;

            case QuoteStatus::STATUS_ORDERED:
                $canOrder['error'] = true;
                $canOrder['error_code'] = self::ERROR_REQUEST_HAS_BEEN_ORDERED;
                $canOrder['error_message'] = __("This quotation has been ordered, please submit a new quote request");
                break;

            default:
                $canOrder['error'] = true;
                $canOrder['error_code'] = self::ERROR_REQUEST_IS_NOT_PROCESSED;
                $canOrder['error_message'] = __("This quote request has not been processed yet, please contact the store manager");
                break;
        }

        if($canOrder['error'] == true){
            throw new \Magento\Framework\Exception\ValidatorException($canOrder['error_message'], null, $canOrder['error_code']);
        }
        return true;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return array
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function canCheckout(\Magento\Quote\Api\Data\CartInterface $quote){
        $canCheckout = $this->canView($quote);
        if($canCheckout['error'] === false){
            $this->canOrder($quote);
        }
        return $canCheckout;
    }
}
