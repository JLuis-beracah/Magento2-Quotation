<?php
/**
 *
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Controller\Adminhtml\Quote\Reports;

class Mostrequested extends \Magento\Reports\Controller\Adminhtml\Report\Product
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magestore_Quotation::reports_most_requested';

    /**
     * Sold Products Report Action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_setActiveMenu(
            'Magestore_Quotation::reports_most_requested'
        )->_addBreadcrumb(
            __('Requested Products'),
            __('Requested Products')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Most Requested Products Report'));
        $this->_view->renderLayout();
    }
}
