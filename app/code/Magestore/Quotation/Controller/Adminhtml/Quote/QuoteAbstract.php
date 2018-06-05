<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Controller\Adminhtml\Quote;

use Magento\Backend\Model\View\Result\ForwardFactory;

/**
 * Class QuoteAbstract
 * @package Magestore\Quotation\Controller\Adminhtml\Quote
 */
abstract class QuoteAbstract extends \Magestore\Quotation\Controller\Adminhtml\AbstractAction
{
    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Builder
     */
    protected $productBuilder;

    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    protected $productTypeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * QuoteAbstract constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magestore\Quotation\Helper\Data $helper
     * @param \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement
     * @param \Magestore\Quotation\Model\BackendCart $backendCart
     * @param \Magestore\Quotation\Model\BackendSession $backendSession
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $initializationHelper
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magestore\Quotation\Helper\Data $helper,
        \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement,
        \Magestore\Quotation\Model\BackendCart $backendCart,
        \Magestore\Quotation\Model\BackendSession $backendSession,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $initializationHelper,
        \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ){
        parent::__construct($context, $helper, $quotationManagement, $backendCart, $backendSession, $registry);
        $this->productBuilder = $productBuilder;
        $this->initializationHelper = $initializationHelper;
        $this->productTypeManager = $productTypeManager;
        $this->productRepository = $productRepository;
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
            $this->_initQuote((int)$quoteId);
        }
        return $this;
    }

    /**
     * @param null $quoteId
     * @return $this
     */
    protected function _initQuote($quoteId = null){
        if($quoteId){
            $this->_getSession()->setQuoteId((int)$quoteId);
        }
        $this->_getSession()->reloadQuote();
        $this->quotationManagement->isExpired($this->_getQuote());
        $registryObject = $this->_getRegistry();
        $registryObject->unregister('current_quote_request');
        $registryObject->register('current_quote_request', $this->_getQuote());
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
        $quote = $this->_getQuote();
        if($quote && $quote->getId()){
            $requestAction = $this->getRequest()->getPost('quote_request_action');
            if(!$requestAction){
                $this->_getQuotationManagement()->process($quote);
            }
            if ($requestAction == 'send') {
                $this->_getQuotationManagement()->send($quote);
            }
            if ($requestAction == 'decline') {
                $this->_getQuotationManagement()->decline($quote);
            }
            $expirationDate = $this->getRequest()->getPost('expiration_date');
            if(isset($expirationDate)){
                $this->_getQuotationManagement()->setExpirationDate($quote, $expirationDate);
            }
            $this->_initQuote();
        }
        $this->_getQuoteProcessModel()->setQuote($this->_getQuote());

        /**
         * Adding product to quote from shopping cart, wishlist etc.
         */
        if ($productId = (int)$this->getRequest()->getPost('add_product')) {
            $this->_getQuoteProcessModel()->addProduct($productId, $this->getRequest()->getPostValue());
        }

        /**
         * Adding products to quote from special grid
         */
        if ($this->getRequest()->has('item') && !$this->getRequest()->getPost('update_items') && !($action == 'save')
        ) {
            $items = $this->getRequest()->getPost('item');
            $items = $this->_processFiles($items);
            $this->_getQuoteProcessModel()->addProducts($items);
        }

        /**
         * Update quote items
         */
        if ($this->getRequest()->getPost('update_items')) {
            $items = $this->getRequest()->getPost('item', []);
            $items = $this->_processFiles($items);
            $this->_getQuoteProcessModel()->updateQuoteItems($items);
        }

        /**
         * Remove quote item
         */
        $removeItemId = (int)$this->getRequest()->getPost('remove_item');
        $removeFrom = (string)$this->getRequest()->getPost('from');
        if ($removeItemId && $removeFrom) {
            $this->_getQuoteProcessModel()->removeItem($removeItemId, $removeFrom);
            $this->_getQuoteProcessModel()->recollectCart();
        }

        if ($createProduct = (boolean)$this->getRequest()->getPost('create_product')) {
            $addToQuote = (boolean)$this->getRequest()->getPost('add_to_quote');
            $product = $this->_createProduct();
            if($product && $addToQuote){
                $this->_getQuoteProcessModel()->addProduct($product->getId(), 1);
                $this->_getQuoteProcessModel()->recollectCart();
            }
        }

        $this->_getQuoteProcessModel()->saveQuote();


        return $this;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    protected function _createProduct(){
        try {
            $quote = $this->_getQuote();
            $request = $this->getRequest();
            $params = $request->getParams();
            $params['store'] = $quote->getStoreId();
            $params['type'] = \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE;
            $params['set'] = 4;
            $request->setParams($params);
            $product = $this->initializationHelper->initialize(
                $this->productBuilder->build($this->getRequest())
            );
            $product->setData('product_has_weight', \Magento\Catalog\Model\Product\Edit\WeightResolver::HAS_WEIGHT);
            $this->productTypeManager->processProduct($product);
            try{
                $existProduct = $this->productRepository->get($product->getSku());
                $product = false;
            }catch (\Exception $e){
                $existProduct = false;
            }
            if($existProduct && $existProduct->getId()){
                throw new \Magento\Framework\Exception\LocalizedException(__('The product with SKU %1 already exist.', $existProduct->getSku()));
            }else{
                $this->messageManager->addSuccessMessage(__('The product has been created successfully.'));
                $product->save();
                $this->_eventManager->dispatch(
                    'controller_action_catalog_product_save_entity_after',
                    ['controller' => $this, 'product' => $product]
                );
            }

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->messageManager->addExceptionMessage($e);
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $product;
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
