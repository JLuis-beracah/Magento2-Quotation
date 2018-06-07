<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model\Quote\Comment;

use Magestore\Quotation\Api\Data\QuoteCommentHistoryInterface;
use Magestore\Quotation\Model\Source\Quote\Status as QuoteStatus;

/**
 * Class History
 * @package Magestore\Quotation\Model\Quote\Comment
 */
class History extends \Magento\Framework\Model\AbstractExtensibleModel implements QuoteCommentHistoryInterface
{
    const CUSTOMER_NOTIFICATION_NOT_APPLICABLE = 2;

    /**
     * Quote instance
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;

    /**
     * @var string
     */
    protected $_eventPrefix = 'quotation_quote_comment_history';

    /**
     * @var string
     */
    protected $_eventObject = 'status_history';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_storeManager = $storeManager;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magestore\Quotation\Model\ResourceModel\Quote\Comment\History::class);
    }

    /**
     * Returns _eventObject
     *
     * @return string
     */
    public function getEventObject()
    {
        return $this->_eventObject;
    }

    /**
     * Set quote object and grab some metadata from it
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     */
    public function setQuote(\Magento\Quote\Model\Quote $quote)
    {
        $this->_quote = $quote;
        $this->setStoreId($quote->getStoreId());
        return $this;
    }

    /**
     * Notification flag
     *
     * @param  mixed $flag OPTIONAL (notification is not applicable by default)
     * @return $this
     */
    public function setIsCustomerNotified($flag = null)
    {
        if ($flag === null) {
            $flag = self::CUSTOMER_NOTIFICATION_NOT_APPLICABLE;
        }

        return $this->setData('is_customer_notified', $flag);
    }

    /**
     * Customer Notification Applicable check method
     *
     * @return boolean
     */
    public function isCustomerNotificationNotApplicable()
    {
        return $this->getIsCustomerNotified() == self::CUSTOMER_NOTIFICATION_NOT_APPLICABLE;
    }

    /**
     * Retrieve quote instance
     *
     * @codeCoverageIgnore
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    /**
     * Retrieve status label
     *
     * @return string|null
     */
    public function getStatusLabel()
    {
        $status = $this->getStatus();
        $statusList = QuoteStatus::getOptionArray();

        return ($status && isset($statusList[$status]))?$statusList[$status]:"";
    }

    /**
     * Get store object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        if ($this->getQuote()) {
            return $this->getQuote()->getStore();
        }
        return $this->_storeManager->getStore();
    }

    /**
     * Set order again if required
     *
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();

        if (!$this->getParentId() && $this->getQuote()) {
            $this->setParentId($this->getQuote()->getId());
        }

        return $this;
    }

    //@codeCoverageIgnoreStart

    /**
     * Returns comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->getData(QuoteCommentHistoryInterface::COMMENT);
    }

    /**
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(QuoteCommentHistoryInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(QuoteCommentHistoryInterface::CREATED_AT, $createdAt);
    }

    /**
     * Returns entity_id
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(QuoteCommentHistoryInterface::ENTITY_ID);
    }

    /**
     * Returns is_customer_notified
     *
     * @return int
     */
    public function getIsCustomerNotified()
    {
        return $this->getData(QuoteCommentHistoryInterface::IS_CUSTOMER_NOTIFIED);
    }

    /**
     * Returns is_visible_on_front
     *
     * @return int
     */
    public function getIsVisibleOnFront()
    {
        return $this->getData(QuoteCommentHistoryInterface::IS_VISIBLE_ON_FRONT);
    }

    /**
     * Returns parent_id
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->getData(QuoteCommentHistoryInterface::PARENT_ID);
    }

    /**
     * Returns status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(QuoteCommentHistoryInterface::STATUS);
    }

    /**
     *
     * @param string
     * @return $this
     */
    public function getCreatedBy()
    {
        return $this->getData(QuoteCommentHistoryInterface::CREATED_BY);
    }

    /**
     * {@inheritdoc}
     */
    public function setParentId($id)
    {
        return $this->setData(QuoteCommentHistoryInterface::PARENT_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsVisibleOnFront($isVisibleOnFront)
    {
        return $this->setData(QuoteCommentHistoryInterface::IS_VISIBLE_ON_FRONT, $isVisibleOnFront);
    }

    /**
     * {@inheritdoc}
     */
    public function setComment($comment)
    {
        return $this->setData(QuoteCommentHistoryInterface::COMMENT, $comment);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        return $this->setData(QuoteCommentHistoryInterface::STATUS, $status);
    }

    /**
     *
     * @param string $createdBy
     * @return $this
     */
    public function setCreatedBy($createdBy){
        return $this->setData(QuoteCommentHistoryInterface::CREATED_BY, $createdBy);
    }
}
