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
        $setup->endSetup();
    }
}
