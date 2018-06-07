<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Quotation\Block\Quote;

use Magento\Quote\Api\Data\CartItemInterface;
use Magestore\Quotation\Model\Source\Quote\Status as QuoteStatus;

/**
 * Class Items
 * @package Magestore\Quotation\Block\Quote
 */
class Items extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Quote items per page.
     *
     * @var int
     */
    private $itemsPerPage;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\Collection|null
     */
    private $itemCollection;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory|null $itemCollectionFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = [],
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollectionFactory = null
    ) {
        $this->_coreRegistry = $registry;
        $this->itemCollectionFactory = $itemCollectionFactory ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory::class);
        parent::__construct($context, $data);
    }

    /**
     * Init pager block and item collection with page size and current page number
     *
     * @return $this
     * @since 100.1.7
     */
    protected function _prepareLayout()
    {
        $this->itemsPerPage = 10;
        $this->itemCollection = $this->itemCollectionFactory->create();
        $this->itemCollection->addFieldToFilter(CartItemInterface::KEY_QUOTE_ID, $this->getQuote()->getId());

        /** @var \Magento\Theme\Block\Html\Pager $pagerBlock */
        $pagerBlock = $this->getChildBlock('quotation_quote_item_pager');
        if ($pagerBlock) {
            $pagerBlock->setLimit($this->itemsPerPage);
            //here pager updates collection parameters
            $pagerBlock->setCollection($this->itemCollection);
            $pagerBlock->setAvailableLimit([$this->itemsPerPage]);
            $pagerBlock->setShowAmounts($this->isPagerDisplayed());
        }

        return parent::_prepareLayout();
    }

    /**
     * Determine if the pager should be displayed for order items list
     * To be called from templates(after _prepareLayout())
     *
     * @return bool
     * @since 100.1.7
     */
    public function isPagerDisplayed()
    {
        $pagerBlock = $this->getChildBlock('quotation_quote_item_pager');
        return $pagerBlock && ($this->itemCollection->getSize() > $this->itemsPerPage);
    }

    /**
     * Get visible items for current page.
     * To be called from templates(after _prepareLayout())
     *
     * @return \Magento\Framework\DataObject[]
     * @since 100.1.7
     */
    public function getItems()
    {
        return $this->itemCollection->getItems();
    }

    /**
     * Get pager HTML according to our requirements
     * To be called from templates(after _prepareLayout())
     *
     * @return string HTML output
     * @since 100.1.7
     */
    public function getPagerHtml()
    {
        /** @var \Magento\Theme\Block\Html\Pager $pagerBlock */
        $pagerBlock = $this->getChildBlock('quotation_quote_item_pager');
        return $pagerBlock ? $pagerBlock->toHtml() : '';
    }

    /**
     * Retrieve current quote model instance
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->_coreRegistry->registry('current_quote');
    }

    /**
     * @return bool
     */
    public function canShowPrice(){
        $quote = $this->getQuote();
        return(
            ($quote->getRequestStatus() == QuoteStatus::STATUS_PROCESSED) ||
            ($quote->getRequestStatus() == QuoteStatus::STATUS_ORDERED)
        )?true:false;
    }
}
