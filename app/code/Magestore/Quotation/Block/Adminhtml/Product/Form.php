<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Quotation\Block\Adminhtml\Product;

/**
 * Class Form
 * @package Magestore\Quotation\Block\Adminhtml\Product
 */
class Form extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magestore\Quotation\Model\Source\Producttaxclass
     */
    protected $taxclassSource;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magestore\Quotation\Model\Source\Producttaxclass $taxclassSource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magestore\Quotation\Model\Source\Producttaxclass $taxclassSource,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->taxclassSource = $taxclassSource;
    }

    /**
     * @return array
     */
    public function getTaxClasses(){
        return $this->taxclassSource->toOptionArray();
    }
}
