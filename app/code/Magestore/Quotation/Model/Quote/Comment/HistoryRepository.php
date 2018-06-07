<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model\Quote\Comment;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magestore\Quotation\Api\Data\QuoteCommentHistoryInterface;
use Magestore\Quotation\Api\Data\QuoteCommentHistoryInterfaceFactory;
use Magestore\Quotation\Api\QuoteCommentHistoryRepositoryInterface;

class HistoryRepository implements QuoteCommentHistoryRepositoryInterface
{
    /**
     * @var \Magestore\Quotation\Model\ResourceModel\Quote\Comment\History
     */
    private $historyResource;

    /**
     * @var \Magestore\Quotation\Model\ResourceModel\Quote\Comment\History\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var QuoteCommentHistoryInterfaceFactory
     */
    private $historyFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * HistoryRepository constructor.
     * @param \Magestore\Quotation\Model\ResourceModel\Quote\Comment\History $historyResource
     * @param \Magestore\Quotation\Model\ResourceModel\Quote\Comment\History\CollectionFactory $collectionFactory
     * @param QuoteCommentHistoryInterfaceFactory $historyFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \Magestore\Quotation\Model\ResourceModel\Quote\Comment\History $historyResource,
        \Magestore\Quotation\Model\ResourceModel\Quote\Comment\History\CollectionFactory $collectionFactory,
        QuoteCommentHistoryInterfaceFactory $historyFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {

        $this->historyResource = $historyResource;
        $this->collectionFactory = $collectionFactory;
        $this->historyFactory = $historyFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {

    }

    /**
     * @inheritdoc
     */
    public function createNew()
    {
        return $this->historyFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        $entity = $this->historyFactory->create();
        $this->historyResource->load($entity, $id);
        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function delete(QuoteCommentHistoryInterface $entity)
    {
        try {
            $this->historyResource->delete($entity);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete the quote comment history.'), $e);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function save(QuoteCommentHistoryInterface $entity)
    {
        try {
            $this->historyResource->save($entity);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save the quote comment history.'), $e);
        }
        return $entity;
    }

    /**
     * @return \Magestore\Quotation\Model\ResourceModel\Quote\Comment\History\Collection
     */
    public function getCollection(){
        return $this->collectionFactory->create();
    }
}
