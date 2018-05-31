<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Controller\Adminhtml\Quote;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;

/**
 * Class QuoteAbstract
 * @package Magestore\Quotation\Controller\Adminhtml\Quote
 */
abstract class QuoteAbstract extends \Magestore\Quotation\Controller\Adminhtml\AbstractAction
{
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * QuoteAbstract constructor.
     * @param Action\Context $context
     * @param \Magestore\Quotation\Helper\Data $helper
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magestore\Quotation\Helper\Data $helper,
        \Magento\Framework\Escaper $escaper
    ) {
        parent::__construct($context, $helper);
        $this->escaper = $escaper;
    }

    /**
     * @return \Magento\Backend\Model\Session|mixed
     */
    protected function _getSession()
    {
        return $this->_objectManager->get(\Magestore\Quotation\Model\BackendSession::class);
    }

    /**
     * Retrieve quote object
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function _getQuote()
    {
        return $this->_getSession()->getQuote();
    }

    /**
     * Retrieve order create model
     *
     * @return \Magestore\Quotation\Model\BackendCart
     */
    protected function _getOrderCreateModel()
    {
        return $this->_objectManager->get(\Magestore\Quotation\Model\BackendCart::class);
    }

    /**
     * Initialize quote session data
     *
     * @return $this
     */
    protected function _initSession()
    {
        /**
         * Init quote
         */
        if ($quoteId = $this->getRequest()->getParam('quote_id')) {
            $this->_getSession()->setQuoteId((int)$quoteId);
            $model = $this->_objectManager->create('Magento\Quote\Model\Quote');
            $registryObject = $this->_objectManager->get('Magento\Framework\Registry');
            $model = $model->load($quoteId);
            if ($model->getId()) {
                $registryObject->register('current_quote_request', $model);
            }
        }
        return $this;
    }

    /**
     * Processing request data
     *
     * @return $this
     */
    protected function _processData()
    {
        return $this->_processActionData();
    }

    /**
     * Process request data with additional logic for saving quote and creating order
     *
     * @param string $action
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _processActionData($action = null)
    {
        $this->_getOrderCreateModel()->setQuote($this->_getQuote());
        /**
         * Adding product to quote from shopping cart, wishlist etc.
         */
        if ($productId = (int)$this->getRequest()->getPost('add_product')) {
            $this->_getOrderCreateModel()->addProduct($productId, $this->getRequest()->getPostValue());
        }

        /**
         * Adding products to quote from special grid
         */
        if ($this->getRequest()->has('item') && !$this->getRequest()->getPost('update_items') && !($action == 'save')
        ) {
            $items = $this->getRequest()->getPost('item');
            $items = $this->_processFiles($items);
            $this->_getOrderCreateModel()->addProducts($items);
        }

        /**
         * Update quote items
         */
        if ($this->getRequest()->getPost('update_items')) {
            $items = $this->getRequest()->getPost('item', []);
            $items = $this->_processFiles($items);
            $this->_getOrderCreateModel()->updateQuoteItems($items);
        }

        /**
         * Remove quote item
         */
        $removeItemId = (int)$this->getRequest()->getPost('remove_item');
        $removeFrom = (string)$this->getRequest()->getPost('from');
        if ($removeItemId && $removeFrom) {
            $this->_getOrderCreateModel()->removeItem($removeItemId, $removeFrom);
            $this->_getOrderCreateModel()->recollectCart();
        }

        $this->_getOrderCreateModel()->saveQuote();


        return $this;
    }

    /**
     * Process buyRequest file options of items
     *
     * @param array $items
     * @return array
     */
    protected function _processFiles($items)
    {
        /* @var $productHelper \Magento\Catalog\Helper\Product */
        $productHelper = $this->_objectManager->get(\Magento\Catalog\Helper\Product::class);
        foreach ($items as $id => $item) {
            $buyRequest = new \Magento\Framework\DataObject($item);
            $params = ['files_prefix' => 'item_' . $id . '_'];
            $buyRequest = $productHelper->addParamsToBuyRequest($buyRequest, $params);
            if ($buyRequest->hasData()) {
                $items[$id] = $buyRequest->toArray();
            }
        }
        return $items;
    }

    /**
     * @return $this
     */
    protected function _reloadQuote()
    {
        $id = $this->_getQuote()->getId();
        $this->_getQuote()->load($id);
        return $this;
    }
}
