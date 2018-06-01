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
     * QuotationManagement constructor.
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $collectionFactory
    ) {
        $this->eventManager = $eventManager;
        $this->quoteRepository = $quoteRepository;
        $this->collectionFactory = $collectionFactory;
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
}
