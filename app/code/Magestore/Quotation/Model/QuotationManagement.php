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
        $quote->setData("request_status", QuoteStatus::STATUS_PENDING);
        $quote->setIsActive(false);
        if($quote->getId()){
            $this->quoteRepository->save($quote);
        }
        return $quote;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function submit(\Magento\Quote\Api\Data\CartInterface $quote){
        $quote->setData("request_status", QuoteStatus::STATUS_NEW);
        $quote->setIsActive(false);
        $items = $quote->getAllItems();
        if(!empty($items)){
            foreach ($items as $item){
                $item->setData("request_status", QuoteStatus::STATUS_PROCESSING);
            }
        }
        if($quote->getId()){
            $this->quoteRepository->save($quote);
        }
        return $quote;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function process(\Magento\Quote\Api\Data\CartInterface $quote){
        $quote->setData("request_status", QuoteStatus::STATUS_PROCESSING);
        $quote->setIsActive(false);
        if($quote->getId()){
            $this->quoteRepository->save($quote);
        }
        return $quote;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function send(\Magento\Quote\Api\Data\CartInterface $quote){
        $quote->setData("request_status", QuoteStatus::STATUS_PROCESSED);
        $quote->setIsActive(false);
        $items = $quote->getAllItems();
        if(!empty($items)){
            foreach ($items as $item){
                $item->setData("request_status", QuoteStatus::STATUS_PROCESSED);
            }
        }
        if($quote->getId()){
            $this->quoteRepository->save($quote);
        }
        return $quote;
    }
}
