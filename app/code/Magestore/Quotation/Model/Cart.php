<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Cart
 * @package Magestore\Quotation\Model
 */
class Cart extends \Magento\Checkout\Model\Cart
{
    /**
     * Cart constructor.
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\ResourceModel\Cart $resourceCart
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magestore\Quotation\Model\Session $quotationSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\ResourceModel\Cart $resourceCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magestore\Quotation\Model\Session $quotationSession,
        array $data = []
    ) {
        parent::__construct($eventManager, $scopeConfig, $storeManager, $resourceCart, $checkoutSession, $customerSession, $messageManager, $stockRegistry, $stockState, $quoteRepository, $productRepository, $data);
        $this->quotationSession = $quotationSession;
    }

    /**
     * @return \Magento\Quote\Model\Quote|mixed
     */
    public function getQuote()
    {
        if (!$this->hasData('quotation_quote')) {
            $this->setData('quotation_quote', $this->quotationSession->getQuote());
        }
        return $this->_getData('quotation_quote');
    }

    /**
     * Set quote object associated with the cart
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     * @codeCoverageIgnore
     */
    public function setQuote(\Magento\Quote\Model\Quote $quote)
    {
        $this->setData('quotation_quote', $quote);
        return $this;
    }

    /**
     * Reinitialize cart quote state
     *
     * @return $this
     */
    protected function reinitializeState()
    {
        $quote = $this->getQuote()->setCheckoutMethod('');
        $this->quotationSession->setCartWasUpdated(true);
        // TODO: Move this logic to Multishipping module as plug-in.
        // reset for multiple address checkout
        if ($this->quotationSession->getCheckoutState() !== Session::CHECKOUT_STATE_BEGIN
            && $this->quotationSession->getCheckoutState() !== null) {
            $quote->removeAllAddresses()->removePayment();
            $this->quotationSession->resetCheckout();
        }
        return $this;
    }

    /**
     * Add product to shopping cart (quote)
     *
     * @param int|Product $productInfo
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addProduct($productInfo, $requestInfo = null)
    {
        $product = $this->_getProduct($productInfo);
        $request = $this->_getProductRequest($requestInfo);
        $productId = $product->getId();

        if ($productId) {
            $stockItem = $this->stockRegistry->getStockItem($productId, $product->getStore()->getWebsiteId());
            $minimumQty = $stockItem->getMinSaleQty();
            //If product quantity is not specified in request and there is set minimal qty for it
            if ($minimumQty
                && $minimumQty > 0
                && !$request->getQty()
            ) {
                $request->setQty($minimumQty);
            }
        }

        if ($productId) {
            try {
                $result = $this->getQuote()->addProduct($product, $request);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->quotationSession->setUseNotice(false);
                $result = $e->getMessage();
            }
            /**
             * String we can get if prepare process has error
             */
            if (is_string($result)) {
                if ($product->hasOptionsValidationFail()) {
                    $redirectUrl = $product->getUrlModel()->getUrl(
                        $product,
                        ['_query' => ['startcustomization' => 1]]
                    );
                } else {
                    $redirectUrl = $product->getProductUrl();
                }
                $this->quotationSession->setRedirectUrl($redirectUrl);
                if ($this->quotationSession->getUseNotice() === null) {
                    $this->quotationSession->setUseNotice(true);
                }
                throw new \Magento\Framework\Exception\LocalizedException(__($result));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('The product does not exist.'));
        }

        $this->_eventManager->dispatch(
            'checkout_cart_product_add_after',
            ['quote_item' => $result, 'product' => $product]
        );
        $this->quotationSession->setLastAddedProductId($productId);
        return $this;
    }

    /**
     * Save cart
     *
     * @return $this
     */
    public function save()
    {
        $this->_eventManager->dispatch('checkout_cart_save_before', ['cart' => $this]);

        $this->getQuote()->getBillingAddress();
        $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        $this->getQuote()->collectTotals();
        $this->quoteRepository->save($this->getQuote());
        $this->quotationSession->setQuoteId($this->getQuote()->getId());
        /**
         * Cart save usually called after changes with cart items.
         */
        $this->_eventManager->dispatch('checkout_cart_save_after', ['cart' => $this]);
        $this->reinitializeState();
        return $this;
    }

    /**
     * Get shopping cart items summary (includes config settings)
     *
     * @return int|float
     */
    public function getSummaryQty()
    {
        $quoteId = $this->quotationSession->getQuoteId();

        //If there is no quote id in session trying to load quote
        //and get new quote id. This is done for cases when quote was created
        //not by customer (from backend for example).
        if (!$quoteId && $this->_customerSession->isLoggedIn()) {
            $this->quotationSession->getQuote();
            $quoteId = $this->quotationSession->getQuoteId();
        }

        if ($quoteId && $this->_summaryQty === null) {
            $useQty = $this->_scopeConfig->getValue(
                'checkout/cart_link/use_qty',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $this->_summaryQty = $useQty ? $this->getItemsQty() : $this->getItemsCount();
        }
        return $this->_summaryQty;
    }

    /**
     * Update item in shopping cart (quote)
     * $requestInfo - either qty (int) or buyRequest in form of array or \Magento\Framework\DataObject
     * $updatingParams - information on how to perform update, passed to Quote->updateItem() method
     *
     * @param int $itemId
     * @param int|array|\Magento\Framework\DataObject $requestInfo
     * @param null|array|\Magento\Framework\DataObject $updatingParams
     * @return \Magento\Quote\Model\Quote\Item|string
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @see \Magento\Quote\Model\Quote::updateItem()
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function updateItem($itemId, $requestInfo = null, $updatingParams = null)
    {
        try {
            $item = $this->getQuote()->getItemById($itemId);
            if (!$item) {
                throw new \Magento\Framework\Exception\LocalizedException(__('This quote item does not exist.'));
            }
            $productId = $item->getProduct()->getId();
            $product = $this->_getProduct($productId);
            $request = $this->_getProductRequest($requestInfo);

            if ($productId) {
                $stockItem = $this->stockRegistry->getStockItem($productId, $product->getStore()->getWebsiteId());
                $minimumQty = $stockItem->getMinSaleQty();
                // If product was not found in cart and there is set minimal qty for it
                if ($minimumQty
                    && $minimumQty > 0
                    && !$request->getQty()
                    && !$this->getQuote()->hasProductId($productId)
                ) {
                    $request->setQty($minimumQty);
                }
            }

            $result = $this->getQuote()->updateItem($itemId, $request, $updatingParams);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->quotationSession->setUseNotice(false);
            $result = $e->getMessage();
        }

        /**
         * We can get string if updating process had some errors
         */
        if (is_string($result)) {
            if ($this->quotationSession->getUseNotice() === null) {
                $this->quotationSession->setUseNotice(true);
            }
            throw new \Magento\Framework\Exception\LocalizedException(__($result));
        }

        $this->_eventManager->dispatch(
            'checkout_cart_product_update_after',
            ['quote_item' => $result, 'product' => $product]
        );
        $this->quotationSession->setLastAddedProductId($productId);
        return $result;
    }

    /**
     * @return $this
     */
    public function reset(){
        $this->setData('quotation_quote', null);
        $this->quotationSession->clearStorage();
        return $this;
    }

    /**
     * @return $this
     */
    public function submit(){
        $quote = $this->getQuote();
        $this->quotationSession->setLastRealQuote($quote);
        $this->quotationSession->setLastSuccessQuoteId($quote->getId());
        return $this;
    }
}
