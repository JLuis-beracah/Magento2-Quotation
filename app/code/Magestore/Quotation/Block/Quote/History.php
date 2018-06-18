<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Quote;

use \Magento\Framework\App\ObjectManager;
use \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Magestore\Quotation\Model\Source\Quote\Status as QuoteStatus;

/**
 * Class History
 * @package Magestore\Quotation\Block\Quote
 */
class History extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'quote/history.phtml';

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $_quoteCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Collection
     */
    protected $quotes;

    /**
     * @var CollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var \Magestore\Quotation\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magestore\Quotation\Api\QuotationManagementInterface
     */
    protected $quotationManagement;

    /**
     * History constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CollectionFactory $quoteCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magestore\Quotation\Helper\Data $helper
     * @param \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magestore\Quotation\Helper\Data $helper,
        \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement,
        array $data = []
    ) {
        $this->_quoteCollectionFactory = $quoteCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->helper = $helper;
        $this->quotationManagement = $quotationManagement;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Quotes'));
    }

    /**
     * @return CollectionFactory
     *
     * @deprecated 100.1.1
     */
    private function getQuoteCollectionFactory()
    {
        if ($this->quoteCollectionFactory === null) {
            $this->quoteCollectionFactory = ObjectManager::getInstance()->get(CollectionFactory::class);
        }
        return $this->quoteCollectionFactory;
    }

    /**
     * @return bool|\Magento\Quote\Model\ResourceModel\Quote\Collection
     */
    public function getQuotes()
    {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->quotes) {
            $quotes = $this->getQuoteCollectionFactory()->create()->addFieldToSelect(
                '*'
            )->setOrder(
                'created_at',
                'desc'
            );
            if ($customerId) {
                $quotes->addFieldToFilter('customer_id', $customerId);
            }
            $quotes->addFieldToFilter('request_status', array("neq" => QuoteStatus::STATUS_NONE));
            $quotes->addFieldToFilter('request_status', array("neq" => QuoteStatus::STATUS_PENDING));
            $quotes->addFieldToFilter('request_status', array("neq" => QuoteStatus::STATUS_ADMIN_PENDING));
            $this->quotes = $quotes;
        }
        return $this->quotes;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getQuotes()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'quotation.quote.history.pager'
            )->setCollection(
                $this->getQuotes()
            );
            $this->setChild('pager', $pager);
            $this->getQuotes()->load();
            $this->validateQuotes();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param object $quote
     * @return string
     */
    public function getViewUrl($quote)
    {
        return $this->getUrl('quotation/quote/view', ['quote_id' => $quote->getId()]);
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * @param $quote
     * @return string
     */
    public function getQuoteRequestStatus($quote){
        $statues = QuoteStatus::getOptionArray();
        $requestStatus = $quote->getData('request_status');
        $requestStatusLabel = "";
        if($requestStatus && isset($statues[$requestStatus])){
            $requestStatusLabel = $statues[$requestStatus];
        }
        return $requestStatusLabel;
    }

    /**
     * @param $quote
     * @return bool
     */
    public function canShowPrice($quote){
        return(
            ($quote->getRequestStatus() == QuoteStatus::STATUS_PROCESSED) ||
            ($quote->getRequestStatus() == QuoteStatus::STATUS_ORDERED)
        )?true:false;
    }

    /**
     * @param $quote
     * @param $price
     * @return \Magento\Framework\Phrase|mixed
     */
    public function getPrice($quote, $price){
        $quoteRequestStatus = $quote->getRequestStatus();
        $statusLabels = QuoteStatus::getOptionArray();
        $label = __('Processing');
        if($this->canShowPrice($quote)){
            $label = $this->helper->formatQuotePrice($quote, $price);
        }elseif($quoteRequestStatus && isset($statusLabels[$quoteRequestStatus])){
            $label = $statusLabels[$quoteRequestStatus];
        }
        return $label;
    }

    /**
     * @return $this
     */
    public function validateQuotes(){
        foreach ($this->getQuotes() as $quote){
            $this->quotationManagement->isExpired($quote);
        }
        return $this;
    }
}
