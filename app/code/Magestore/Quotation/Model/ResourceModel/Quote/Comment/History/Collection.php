<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model\ResourceModel\Quote\Comment\History;

use Magento\Quote\Model\Quote;

/**
 * Flat sales order status history collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Quote object
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote = null;

    /**
     * Quote field for setQuoteFilter
     *
     * @var string
     */
    protected $_quoteField = 'parent_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'quotation_quote_comment_history';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'quotation_quote_comment_history_collection';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magestore\Quotation\Model\Quote\Comment\History::class,
            \Magestore\Quotation\Model\ResourceModel\Quote\Comment\History::class
        );
    }

    /**
     * @param $instance
     * @return \Magestore\Quotation\Model\Quote\Comment\History|null
     */
    public function getUnnotifiedForInstance($instance)
    {
        if (!$instance instanceof Quote) {
            $instance = $instance->getQuote();
        }
        $this->setQuoteFilter(
            $instance
        )->setOrder(
            'created_at',
            'desc'
        )->addFieldToFilter(
            'is_customer_notified',
            0
        )->setPageSize(
            1
        );
        foreach ($this->getItems() as $historyItem) {
            return $historyItem;
        }
        return null;
    }

    /**
     * @param Quote $quote
     * @return $this
     */
    public function setQuote(Quote $quote)
    {
        $this->_quote = $quote;
        if ($this->_eventPrefix && $this->_eventObject) {
            $this->_eventManager->dispatch(
                $this->_eventPrefix . '_set_quote',
                ['collection' => $this, $this->_eventObject => $this, 'quote' => $quote]
            );
        }

        return $this;
    }

    /**
     * @return Quote
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    /**
     * @param $quote
     * @return $this
     */
    public function setQuoteFilter($quote)
    {
        if ($quote instanceof \Magento\Quote\Model\Quote) {
            $this->setQuote($quote);
            $quoteId = $quote->getId();
            if ($quoteId) {
                $this->addFieldToFilter($this->_quoteField, $quoteId);
            } else {
                $this->_totalRecords = 0;
                $this->_setIsLoaded(true);
            }
        } else {
            $this->addFieldToFilter($this->_quoteField, $quote);
        }
        return $this;
    }
}
