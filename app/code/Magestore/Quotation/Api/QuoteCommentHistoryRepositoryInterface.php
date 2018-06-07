<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magestore\Quotation\Api;

/**
 * Interface QuoteCommentHistoryRepositoryInterface
 * @package Magestore\Quotation\Api
 */
interface QuoteCommentHistoryRepositoryInterface
{
    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @return \Magestore\Quotation\Api\Data\QuoteCommentHistoryInterface
     */
    public function createNew();

    /**
     * @param int $id
     * @return \Magestore\Quotation\Api\Data\QuoteCommentHistoryInterface
     */
    public function get($id);

    /**
     * @param \Magestore\Quotation\Api\Data\\QuoteCommentHistoryInterface $entity
     * @return boolean
     */
    public function delete(\Magestore\Quotation\Api\Data\QuoteCommentHistoryInterface $entity);

    /**
     * @param \Magestore\Quotation\Api\Data\\QuoteCommentHistoryInterface $entity
     * @return \Magestore\Quotation\Api\Data\QuoteCommentHistoryInterface
     */
    public function save(\Magestore\Quotation\Api\Data\QuoteCommentHistoryInterface $entity);

    /**
     * @return \Magestore\Quotation\Model\ResourceModel\Quote\Comment\History\Collection
     */
    public function getCollection();
}
