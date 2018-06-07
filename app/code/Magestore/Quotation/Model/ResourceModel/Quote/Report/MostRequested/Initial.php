<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model\ResourceModel\Quote\Report\MostRequested;

/**
 * Class Initial
 * @package Magestore\Quotation\Model\ResourceModel\Quote\Report\MostRequested
 */
class Initial extends \Magento\Reports\Model\ResourceModel\Report\Collection
{
    /**
     * Report sub-collection class name
     *
     * @var string
     */
    protected $_reportCollection = \Magestore\Quotation\Model\ResourceModel\Quote\Report\MostRequested::class;
}
