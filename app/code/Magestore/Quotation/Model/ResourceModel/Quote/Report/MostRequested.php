<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model\ResourceModel\Quote\Report;

use Magento\Framework\DB\Select;
use Magestore\Quotation\Model\Source\Quote\Status as QuoteStatus;

/**
 * Class MostRequested
 * @package Magestore\Quotation\Model\ResourceModel\Quote\Report
 */
class MostRequested extends \Magento\Reports\Model\ResourceModel\Order\Collection
{
    /**
     * Set Date range to collection
     *
     * @param int $from
     * @param int $to
     * @return $this
     */
    public function setDateRange($from, $to)
    {
        $this->_reset()->addAttributeToSelect(
            '*'
        )->addRequestedQty(
            $from,
            $to
        )->setOrder(
            'requested_qty',
            self::SORT_ORDER_DESC
        );
        return $this;
    }

    /**
     * Add requested qty's
     *
     * @param string $from
     * @param string $to
     * @return $this
     */
    public function addRequestedQty($from = '', $to = '')
    {
        $connection = $this->getConnection();
        $quoteTableAliasName = $connection->quoteIdentifier('quote');

        $quoteJoinCondition = [
            $quoteTableAliasName . '.entity_id = quote_items.quote_id',
            $connection->quoteInto("{$quoteTableAliasName}.request_status <> ?", QuoteStatus::STATUS_NONE),
            $connection->quoteInto("{$quoteTableAliasName}.request_status <> ?", QuoteStatus::STATUS_PENDING)
        ];

        if ($from != '' && $to != '') {
            $fieldName = $quoteTableAliasName . '.created_at';
            $quoteJoinCondition[] = $this->prepareBetweenSql($fieldName, $from, $to);
        }

        $this->getSelect()->reset()->from(
            ['quote_items' => $this->getTable('quote_item')],
            [
                'quote_items_qty' => 'quote_items.qty',
                'quote_items_name' => 'quote_items.name',
                'quote_items_sku' => 'quote_items.sku'
            ]
        )->columns(
            [
                'requested_qty' => 'SUM(quote_items.qty)'
            ]
        )->joinInner(
            ['quote' => $this->getTable('quote')],
            implode(' AND ', $quoteJoinCondition),
            []
        )->where(
            'quote_items.parent_item_id IS NULL'
        )->group("quote_items.sku");
        return $this;
    }

    /**
     * Set store filter to collection
     *
     * @param array $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds)
    {
        if ($storeIds) {
            $this->getSelect()->where('quote_items.store_id IN (?)', (array)$storeIds);
        }
        return $this;
    }

    /**
     * Set order
     *
     * @param string $attribute
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = self::SORT_ORDER_DESC)
    {
        if (in_array($attribute, ['requested_qty'])) {
            $this->getSelect()->order($attribute . ' ' . $dir);
        } else {
            parent::setOrder($attribute, $dir);
        }

        return $this;
    }

    /**
     * @return Select
     * @since 100.2.0
     */
    public function getSelectCountSql()
    {
        $countSelect = clone parent::getSelectCountSql();

        $countSelect->reset(Select::COLUMNS);
        $countSelect->columns('COUNT(DISTINCT quote_items.item_id)');

        return $countSelect;
    }

    /**
     * Prepare between sql
     *
     * @param string $fieldName Field name with table suffix ('created_at' or 'main_table.created_at')
     * @param string $from
     * @param string $to
     * @return string Formatted sql string
     */
    protected function prepareBetweenSql($fieldName, $from, $to)
    {
        return sprintf(
            '(%s BETWEEN %s AND %s)',
            $fieldName,
            $this->getConnection()->quote($from),
            $this->getConnection()->quote($to)
        );
    }
}
