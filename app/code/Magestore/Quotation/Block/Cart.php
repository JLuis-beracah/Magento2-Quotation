<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block;

/**
 * Class Cart
 * @package Magestore\Quotation\Block
 */
class Cart extends \Magestore\Quotation\Block\AbstractCart
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Url
     */
    protected $_catalogUrlBuilder;

    /**
     * AbstractCart constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magestore\Quotation\Model\Session $quotationSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magestore\Quotation\Model\Session $quotationSession,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder,
        array $data = []
    ) {
        $this->_isScopePrivate = true;
        $this->_catalogUrlBuilder = $catalogUrlBuilder;
        parent::__construct($context, $customerSession, $checkoutSession, $quotationSession, $data);
    }


    /**
     * Prepare Quote Item Product URLs
     *
     * @codeCoverageIgnore
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->prepareItemUrls();
    }

    /**
     * prepare cart items URLs
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function prepareItemUrls()
    {
        $products = [];
        /* @var $item \Magento\Quote\Model\Quote\Item */
        foreach ($this->getItems() as $item) {
            $product = $item->getProduct();
            $option = $item->getOptionByCode('product_type');
            if ($option) {
                $product = $option->getProduct();
            }

            if ($item->getStoreId() != $this->_storeManager->getStore()->getId() &&
                !$item->getRedirectUrl() &&
                !$product->isVisibleInSiteVisibility()
            ) {
                $products[$product->getId()] = $item->getStoreId();
            }
        }

        if ($products) {
            $products = $this->_catalogUrlBuilder->getRewriteByProductStore($products);
            foreach ($this->getItems() as $item) {
                $product = $item->getProduct();
                $option = $item->getOptionByCode('product_type');
                if ($option) {
                    $product = $option->getProduct();
                }

                if (isset($products[$product->getId()])) {
                    $object = new \Magento\Framework\DataObject($products[$product->getId()]);
                    $item->getProduct()->setUrlDataObject($object);
                }
            }
        }
    }

    /**
     * @codeCoverageIgnore
     * @return bool
     */
    public function hasError()
    {
        return $this->getQuote()->getHasError();
    }

    /**
     * @codeCoverageIgnore
     * @return int
     */
    public function getItemsSummaryQty()
    {
        return $this->getQuote()->getItemsSummaryQty();
    }

    /**
     * @return string
     */
    public function getContinueShoppingUrl()
    {
        $url = $this->getData('continue_shopping_url');
        if ($url === null) {
            $url = $this->_checkoutSession->getContinueShoppingUrl(true);
            if (!$url) {
                $url = $this->_urlBuilder->getUrl();
            }
            $this->setData('continue_shopping_url', $url);
        }
        return $url;
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsVirtual()
    {
        return $this->getQuote()->getIsVirtualQuote();
    }

    /**
     * Return customer quote items
     *
     * @return array
     */
    public function getItems()
    {
        if ($this->getCustomItems()) {
            return $this->getCustomItems();
        }

        return parent::getItems();
    }

    /**
     * @codeCoverageIgnore
     * @return int
     */
    public function getItemsCount()
    {
        return $this->getQuote()->getItemsCount();
    }

    /**
     * Render pagination HTML
     *
     * @return string
     * @since 100.2.0
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
