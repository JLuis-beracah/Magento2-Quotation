<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model;

use Magestore\Quotation\Model\Source\Quote\Status as QuoteStatus;

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
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * QuotationManagement constructor.
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $collectionFactory
     * @param Quote\Email\Sender $quoteSender
     * @param \Magestore\Quotation\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $collectionFactory,
        \Magestore\Quotation\Model\Quote\Email\Sender $quoteSender,
        \Magestore\Quotation\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->eventManager = $eventManager;
        $this->quoteRepository = $quoteRepository;
        $this->collectionFactory = $collectionFactory;
        $this->quoteSender = $quoteSender;
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
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
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    public function canDecline(\Magento\Quote\Api\Data\CartInterface $quote){
        $requestStatus = $quote->getData("request_status");
        return (
        ($requestStatus == QuoteStatus::STATUS_DECLINED) ||
        ($requestStatus == QuoteStatus::STATUS_EXPIRED) ||
        ($requestStatus == QuoteStatus::STATUS_PROCESSED)
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
        ($requestStatus == QuoteStatus::STATUS_EXPIRED)
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
        ($requestStatus == QuoteStatus::STATUS_PROCESSED)
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
                        $this->customerSession->setData("validating_quote_request_id", $quoteId);
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
        if($removeExistedItems){
            $this->checkoutSession->clearQuote();
        }
        $shoppingCart = $this->checkoutSession->getQuote();
        $shoppingCart->merge($quote);

        $billingAddress = $quote->getBillingAddress();
        if($billingAddress){
            if($billingAddress->getId()){
                $billingAddress->setId(null);
            }
            $shoppingCart->setBillingAddress($billingAddress);
        }

        $shippingAddress = $quote->getShippingAddress();
        if($shippingAddress){
            if($shippingAddress->getId()){
                $shippingAddress->setId(null);
            }
            $shoppingCart->setShippingAddress($shippingAddress);
        }
        $shoppingCart->collectTotals();
        $this->quoteRepository->save($shoppingCart);
        $this->checkoutSession->setQuoteId($shoppingCart->getId());
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
            $isLoggedIn = $this->customerSession->isLoggedIn();
            if($isLoggedIn){
                $customerId = $this->customerSession->getCustomerId();
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
     * @return array
     */
    public function canCheckout(\Magento\Quote\Api\Data\CartInterface $quote){
        $canCheckout = $this->canView($quote);
        if($canCheckout['error'] === false){
            if($quote->getRequestStatus() == QuoteStatus::STATUS_PROCESSED){
                $isExpired = $this->isExpired($quote);
                if($isExpired){
                    $canCheckout['error'] = true;
                    $canCheckout['error_code'] = self::ERROR_REQUEST_EXPIRED;
                    $canCheckout['error_message'] = __("This quotation has been expired");
                }
            }else{
                $canCheckout['error'] = true;
                $canCheckout['error_code'] = self::ERROR_REQUEST_IS_NOT_PROCESSED;
                $canCheckout['error_message'] = __("This quote request has not been processed, please contact the store manager");
            }
        }
        return $canCheckout;
    }
}
