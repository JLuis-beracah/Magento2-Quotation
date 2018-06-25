<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Quotation\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory;

/**
 * Class Salesrep
 * @package Magestore\Quotation\Ui\Component\Listing\Column
 */
class Salesrep implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $salesreps = [
                ["value" => 0, "label" => __("None")]
            ];
            $collection = $this->collectionFactory->create();
            if($collection->getSize() > 0){
                foreach ($collection as $adminUser){
                    $salesreps[] = [
                        "value" => $adminUser->getId(),
                        "label" => $adminUser->getFirstname()." ".$adminUser->getLastname()
                    ];
                }
            }
            $this->options = $salesreps;
        }
        return $this->options;
    }
}