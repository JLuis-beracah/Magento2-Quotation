<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Quotation\Block\Adminhtml\Widget;

/**
 * Class Grid
 * @package Magestore\Quotation\Block\Widget
 */
class Grid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->setTemplate('Magestore_Quotation::widget/grid.phtml');
    }
}
