<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Adminhtml\Reports;

/**
 * Class MostRequested
 * @package Magestore\Quotation\Block\Adminhtml\Reports
 */
class MostRequested extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     */
    protected $_blockGroup = 'Magestore_Quotation';

    /**
     * Initialize container block settings
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magestore_Quotation';
        $this->_controller = 'quotation_quote_reports_mostrequested';
        $this->_headerText = __('Products Requested');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
