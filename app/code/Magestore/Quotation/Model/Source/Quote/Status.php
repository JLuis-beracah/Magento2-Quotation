<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model\Source\Quote;

/**
 * Class Status
 * @package Magestore\Quotation\Model\Source\Quote
 */
class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    const STATUS_NONE = 0;
    const STATUS_PENDING = 1;
    const STATUS_NEW = 2;
    const STATUS_PROCESSING = 3;
    const STATUS_PROCESSED = 4;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('New'), 'value' => self::STATUS_NEW],
            ['label' => __('Processing'), 'value' => self::STATUS_PROCESSING],
            ['label' => __('Processed'), 'value' => self::STATUS_PROCESSED]
        ];
    }

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [self::STATUS_NEW => __('New'), self::STATUS_PROCESSING => __('Processing'), self::STATUS_PROCESSED => __('Processed')];
    }

}