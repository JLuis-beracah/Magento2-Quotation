<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Controller\Adminhtml\Quote;

use Magento\Backend\Model\View\Result\ForwardFactory;
use Magestore\Quotation\Model\CustomProduct\Type as CustomProductType;
use Magestore\Quotation\Model\Source\Quote\Status as QuoteStatus;

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
        $editQuoteId = $this->getRequest()->getParam('id');
        $quoteId = $this->getRequest()->getParam('quote_id');
        $customerId = $this->getRequest()->getParam('customer_id');
        $currencyId = $this->getRequest()->getParam('currency_id');
        $storeId = $this->getRequest()->getParam('store_id');
        $isCreatingNewQuote = ($editQuoteId != null)?false:true;

        $generalSession = $this->_getQuoteProcessModel()->getGeneralSession();
        $quoteSession = $this->_getSession();


        $newQuotationQuoteId = $generalSession->getNewQuotationQuoteId();
        $newQuotationCustomerId = $generalSession->getNewQuotationCustomerId();
        $newQuotationCurrencyId = $generalSession->getNewQuotationCurrencyId();
        $newQuotationStoreId = $generalSession->getNewQuotationStoreId();

        if($isCreatingNewQuote){
            if($quoteId  == null){
                $quoteId = ($newQuotationQuoteId)?$newQuotationQuoteId:$quoteId;
                $customerId = (!$customerId && $newQuotationCustomerId)?$newQuotationCustomerId:$customerId;
                $currencyId = (!$currencyId && $newQuotationCurrencyId)?$newQuotationCurrencyId:$currencyId;
                $storeId = (!$storeId && $newQuotationStoreId)?$newQuotationStoreId:$storeId;
            }
            if(!empty($quoteId)){
                $isNew = $quoteSession->isNewAdminQuote((int)$quoteId);
                if(!$isNew){
                    $quoteId = null;
                    $quoteSession->reset();
                    $this->resetNewQuotationSession();
                }
            }else{
                $quoteSession->setQuoteId(null);
                $generalSession->setNewQuotationQuoteId(null);
            }
            /**
             * Identify customer
             */
            if ($customerId != null) {
                $quoteSession->setCustomerId((int)$customerId);
                if(!$quoteId){
                    $generalSession->setNewQuotationCustomerId((int)$customerId);
                }
            }else{
                $quoteSession->setCustomerId(null);
                $generalSession->setNewQuotationCustomerId(null);
            }

            /**
             * Identify store
             */
            if ($storeId != null) {
                $quoteSession->setStoreId((int)$storeId);
                if(!$quoteId){
                    $generalSession->setNewQuotationStoreId((int)$storeId);
                }
            }else{
                $quoteSession->setStoreId(null);
                $generalSession->setNewQuotationStoreId(null);
            }

            /**
             * Identify currency
             */
            if ($currencyId != null) {
                $quoteSession->setCurrencyId((string)$currencyId);
                $this->_getQuoteProcessModel()->setRecollect(true);
                if(!$quoteId){
                    $generalSession->setNewQuotationCurrencyId((string)$currencyId);
                }
            }else{
                $quoteSession->setCurrencyId(null);
                $generalSession->setNewQuotationCurrencyId(null);
            }
        }else{
            $quoteId = ($editQuoteId)?$editQuoteId:$quoteId;
            $quote = $this->quotationManagement->getQuoteRequest($quoteId);
            if($quote){
                $customerId = ($customerId)?$customerId:$quote->getCustomerId();
                if ((int)$customerId > 0) {
                    $quoteSession->setCustomerId((int)$customerId);
                }else{
                    $quoteSession->setCustomerId(null);
                }

                $storeId = ($storeId)?$storeId:$quote->getStoreId();
                if ((int)$storeId) {
                    $quoteSession->setStoreId((int)$storeId);
                }else{
                    $quoteSession->setStoreId(null);
                }

                $currencyId = ($currencyId)?$currencyId:$quote->getCurrency()->getId();
                if ($currencyId) {
                    $quoteSession->setCurrencyId($currencyId);
                }else{
                    $quoteSession->setCurrencyId(null);
                }
            }
        }


        /**
         * Init quote
         */
        if ($quoteId != null) {
            $this->_initQuote((int)$quoteId);
        }else{
            $this->_initQuote();
        }
        return $this;
    }

    /**
     * @param null $quoteId
     * @return $this
     */
    protected function _initQuote($quoteId = null){
        $quoteSession = $this->_getSession();
        if($quoteId != null){
            $quoteSession->setQuoteId((int)$quoteId);
            $quoteSession->reloadQuote();
            $quote = $this->_getQuote();
//            if($quote->getRequestStatus() != QuoteStatus::STATUS_ADMIN_PENDING){
//                $quoteSession->reset();
//                $quote = $this->_getQuote();
//            }
            if(!$quote->getId()){
                $this->messageManager->addErrorMessage(__('This quote request no longer exists.'));
                return $this->createRedirectResult()->setPath('quotation/quote/', ['_current' => true]);
            }
            if($quote->getRequestStatus() == QuoteStatus::STATUS_PROCESSED){
                $this->quotationManagement->isExpired($quote);
                $quoteSession->reloadQuote();
            }
        }else{
            $quote = $this->_getQuote();
            $this->_getSession()->reAssignCustomer();
            if($quote->getId()){
                $generalSession = $this->_getQuoteProcessModel()->getGeneralSession();
                $generalSession->setNewQuotationQuoteId($quote->getId());
            }
        }
        $quote = $this->_getQuote();
        $quote->getBillingAddress();
        $quote->getShippingAddress();
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $quote->setData($data);
        }
        if($customerId = $quote->getCustomerId()){
            $this->_getSession()->setCustomerId((int)$customerId);
        }
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

            if(in_array($requestAction, ['send', 'submit'])){
                $this->_getQuoteProcessModel()->checkAndCreateCustomerAccount($quote);
                if ($requestAction == 'send') {
                    $this->_getQuotationManagement()->send($quote);
                }
                if ($requestAction == 'submit') {
                    $this->_getQuotationManagement()->submit($quote);
                }
            }
            if ($requestAction == 'decline') {
                $this->_getQuotationManagement()->decline($quote);
            }
            $expirationDate = $this->getRequest()->getPost('expiration_date');
            if(isset($expirationDate)){
                $this->_getQuotationManagement()->setExpirationDate($quote, $expirationDate);
            }
            $salesrep = $this->getRequest()->getPost('salesrep');
            if(isset($salesrep)){
                $this->_getQuotationManagement()->setSalesrep($quote, $salesrep);
            }
            $recipientEmails = $this->getRequest()->getPost('additional_recipient_emails');
            if(isset($recipientEmails)){
                $this->_getQuotationManagement()->setRecipientEmails($quote, $recipientEmails);
            }
            $this->_initQuote();
        }
        $this->_getQuoteProcessModel()->setQuote($this->_getQuote());

        /**
         * Import post data, in order to make quote valid
         */
        if ($data = $this->getRequest()->getPost('quote')) {
            $this->_getQuoteProcessModel()->importPostData($data);
            if(isset($data['account'])){
                if($quote->getCustomerId()){
                    $this->_getQuoteProcessModel()->updateCustomerData($data['account']);
                }else{
                    if(isset($requestAction) && in_array($requestAction, ['send', 'submit'])){
                        if(isset($data['account']['email']) && ($quote->getRequestStatus() == QuoteStatus::STATUS_ADMIN_PENDING)){
                            $this->_getQuoteProcessModel()->validateNewCustomerEmail($data['account']['email']);
                        }
                    }
                }
            }
        }

        /**
         * Initialize catalog rule data
         */
        $this->_getQuoteProcessModel()->initRuleData();

        /**
         * init first billing address, need for virtual products
         */
        $this->_getQuoteProcessModel()->getBillingAddress();

        /**
         * Flag for using billing address for shipping
         */
        if (!$this->_getQuoteProcessModel()->getQuote()->isVirtual()) {
            $syncFlag = $this->getRequest()->getPost('shipping_as_billing');
            $shippingMethod = $this->_getQuoteProcessModel()->getShippingAddress()->getShippingMethod();
            if ($syncFlag === null
                && $this->_getQuoteProcessModel()->getShippingAddress()->getSameAsBilling() && empty($shippingMethod)
            ) {
                $this->_getQuoteProcessModel()->setShippingAsBilling(1);
            } else {
                $this->_getQuoteProcessModel()->setShippingAsBilling((int)$syncFlag);
            }
        }

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

        if ($addCustomProduct = (boolean)$this->getRequest()->getPost('add_custom_product')) {
            $this->_addCustomProduct();
        }

        /**
         * Change shipping address flag
         */
        if (!$this->_getQuoteProcessModel()->getQuote()->isVirtual() && $this->getRequest()->getPost('reset_shipping')
        ) {
            $this->_getQuoteProcessModel()->resetShippingMethod(true);
        }

        /**
         * Collecting shipping rates
         */
        if (!$this->_getQuoteProcessModel()->getQuote()->isVirtual() && $this->getRequest()->getPost(
                'collect_shipping_rates'
            )
        ) {
            $this->_getQuoteProcessModel()->collectShippingRates();
        }
        $this->_getQuoteProcessModel()->saveQuote();

        $data = $this->getRequest()->getPost('quote');
        $couponCode = '';
        if (isset($data) && isset($data['coupon']['code'])) {
            $couponCode = trim($data['coupon']['code']);
        }

        if (!empty($couponCode)) {
            $isApplyDiscount = false;
            foreach ($this->_getQuote()->getAllItems() as $item) {
                if (!$item->getNoDiscount()) {
                    $isApplyDiscount = true;
                    break;
                }
            }
            if (!$isApplyDiscount) {
                $this->messageManager->addError(
                    __(
                        '"%1" coupon code was not applied. Do not apply discount is selected for item(s)',
                        $this->helper->escapeHtml($couponCode)
                    )
                );
            } else {
                if ($this->_getQuote()->getCouponCode() !== $couponCode) {
                    $this->messageManager->addError(
                        __(
                            '"%1" coupon code is not valid.',
                            $this->helper->escapeHtml($couponCode)
                        )
                    );
                } else {
                    $this->messageManager->addSuccess(__('The coupon code "%1" has been accepted.', $couponCode));
                }
            }
        }
        if ($adminSubmit = (boolean)$this->getRequest()->getPost('clear_session')) {
            $this->_getSession()->reset();
            $this->resetNewQuotationSession();
        }
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
     * Add custom product
     */
    protected function _addCustomProduct(){
        try {
            $request = $this->getRequest();
            $params = $request->getParam("product");
            try{
                $product = $this->productRepository->get(CustomProductType::DEFAULT_CUSTOM_PRODUCT_SKU);
                $buyRequest['options'] = $params;
                $buyRequest["qty"] = 1;
            }catch (\Exception $e){
                $product = false;
            }
            if($product){
                $this->_getQuoteProcessModel()->addProduct($product->getId(), $buyRequest);
                $this->_getQuoteProcessModel()->recollectCart();
            }

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->messageManager->addExceptionMessage($e);
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
        }
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

    /**
     * @return $this
     */
    public function resetNewQuotationSession(){
        $generalSession = $this->_getQuoteProcessModel()->getGeneralSession();
        $generalSession->reset();
        return $this;
    }
}
