<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Quotation\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magestore\Quotation\Model\Source\Quote\Status as QuoteStatus;

/**
 * Class UpgradeSchema
 * @package Magestore\Quotation\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('quote'),
                    'request_status',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        'length' => 1,
                        'comment' => 'Quote Request Status',
                        'default' => QuoteStatus::STATUS_NONE
                    ]
                );
        }
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('quote_item'),
                    'request_status',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        'length' => 1,
                        'comment' => 'Quote Item Request Status',
                        'default' => QuoteStatus::STATUS_NONE
                    ]
                );

            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('quote'),
                    'expiration_date',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                        'nullable' => true,
                        'comment' => 'Quote Request Expiration Date'
                    ]
                );
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('quote'),
                    'email_sent',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        'unsigned' => true,
                        'comment' => 'Email Sent'
                    ]
                );
        }

        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('quote'),
                    'quotation_request_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => true,
                        'default' => 0,
                        'comment' => 'Quotation Request Id'
                    ]
                );

            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('sales_order'),
                    'quotation_request_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => true,
                        'default' => 0,
                        'comment' => 'Quotation Request Id'
                    ]
                );
        }

        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('quote'),
                    'request_ordered_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => true,
                        'default' => 0,
                        'comment' => 'Request Ordered Id'
                    ]
                );

            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('quote'),
                    'request_ordered_increment_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 32,
                        'nullable' => true,
                        'default' => "",
                        'comment' => 'Request Ordered Increment Id'
                    ]
                );
        }

        if (version_compare($context->getVersion(), '1.0.6', '<')) {

            $table = $setup->getConnection()->newTable(
                $setup->getTable('quotation_quote_comment_history')
            )->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity Id'
            )->addColumn(
                'parent_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Parent Id'
            )->addColumn(
                'is_customer_notified',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'Is Customer Notified'
            )->addColumn(
                'is_visible_on_front',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Is Visible On Front'
            )->addColumn(
                'comment',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Comment'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => QuoteStatus::STATUS_NONE],
                'Status'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addIndex(
                $setup->getIdxName('quotation_quote_comment_history', ['parent_id']),
                ['parent_id']
            )->addIndex(
                $setup->getIdxName('quotation_quote_comment_history', ['created_at']),
                ['created_at']
            )->addForeignKey(
                $setup->getFkName('quotation_quote_comment_history', 'parent_id', 'sales_order', 'entity_id'),
                'parent_id',
                $setup->getTable('quote'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'Quotation Quote Comment History'
            );
            $setup->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.0.7', '<')) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('quotation_quote_comment_history'),
                    'created_by',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 32,
                        'comment' => 'Quote Comment Created By',
                        'nullable' => true,
                        'default' => ""
                    ]
                );
        }

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('quote'),
                    'salesrep',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => true,
                        'default' => 0,
                        'comment' => 'Sales Representative'
                    ]
                );
        }

        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('quote_item'),
                    'admin_discount_percentage',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => true,
                        'default' => 0,
                        'comment' => 'Admin Discount Percentage'
                    ]
                );
        }
        $setup->endSetup();
    }
}
