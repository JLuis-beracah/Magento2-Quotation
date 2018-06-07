<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Api\Data;

/**
 * Interface QuoteCommentHistoryInterface
 * @package Magestore\Quotation\Api\Data
 */
interface QuoteCommentHistoryInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    /*
     * Entity ID.
     */
    const ENTITY_ID = 'entity_id';
    /*
     * Parent ID.
     */
    const PARENT_ID = 'parent_id';
    /*
     *  Is-customer-notified flag.
     */
    const IS_CUSTOMER_NOTIFIED = 'is_customer_notified';
    /*
     * Is-visible-on-storefront flag.
     */
    const IS_VISIBLE_ON_FRONT = 'is_visible_on_front';
    /*
     * Comment.
     */
    const COMMENT = 'comment';
    /*
     * Status.
     */
    const STATUS = 'status';
    /*
     * Create-at timestamp.
     */
    const CREATED_AT = 'created_at';

    /*
     * Created By
     */
    const CREATED_BY = 'created_by';

    /**#@+
     * Constants for default value
     */
    const ADMIN = "admin";
    const CUSTOMER = "customer";

    /**
     * Gets the comment for the quote status history.
     *
     * @return string Comment.
     */
    public function getComment();

    /**
     * Gets the created-at timestamp for the quote status history.
     *
     * @return string|null Created-at timestamp.
     */
    public function getCreatedAt();

    /**
     * Sets the created-at timestamp for the quote status history.
     *
     * @param string $createdAt timestamp
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Gets the ID for the quote status history.
     *
     * @return int|null Order status history ID.
     */
    public function getEntityId();

    /**
     * Sets entity ID.
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Gets the is-customer-notified flag value for the quote status history.
     *
     * @return int Is-customer-notified flag value.
     */
    public function getIsCustomerNotified();

    /**
     * Gets the is-visible-on-storefront flag value for the quote status history.
     *
     * @return int Is-visible-on-storefront flag value.
     */
    public function getIsVisibleOnFront();

    /**
     * Gets the parent ID for the quote status history.
     *
     * @return int Parent ID.
     */
    public function getParentId();

    /**
     * Gets the status for the quote status history.
     *
     * @return string|null Status.
     */
    public function getStatus();

    /**
     *
     * @param string
     * @return $this
     */
    public function getCreatedBy();

    /**
     * Sets the parent ID for the quote status history.
     *
     * @param int $id
     * @return $this
     */
    public function setParentId($id);

    /**
     * Sets the is-customer-notified flag value for the quote status history.
     *
     * @param int $isCustomerNotified
     * @return $this
     */
    public function setIsCustomerNotified($isCustomerNotified);

    /**
     * Sets the is-visible-on-storefront flag value for the quote status history.
     *
     * @param int $isVisibleOnFront
     * @return $this
     */
    public function setIsVisibleOnFront($isVisibleOnFront);

    /**
     * Sets the comment for the quote status history.
     *
     * @param string $comment
     * @return $this
     */
    public function setComment($comment);

    /**
     * Sets the status for the quote status history.
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     *
     * @param string $createdBy
     * @return $this
     */
    public function setCreatedBy($createdBy);
    
}
