<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Adminhtml\Quote\Edit;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Form
 */
class Form extends \Magestore\Quotation\Block\Adminhtml\Quote\Edit\AbstractEdit
{
    /**
     * Customer form factory
     *
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $_customerFormFactory;

    /**
     * Json encoder
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * Address service
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    protected $addressMapper;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Registry $registry
     * @param \Magestore\Quotation\Model\BackendSession $quoteSession
     * @param \Magestore\Quotation\Model\BackendCart $quoteCart
     * @param \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Registry $registry,
        \Magestore\Quotation\Model\BackendSession $quoteSession,
        \Magestore\Quotation\Model\BackendCart $quoteCart,
        \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_customerFormFactory = $customerFormFactory;
        $this->customerRepository = $customerRepository;
        $this->_localeCurrency = $localeCurrency;
        $this->addressMapper = $addressMapper;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $registry, $quoteSession, $quoteCart, $quotationManagement, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quotation_quote_edit_form');
    }

    /**
     * Retrieve url for loading blocks
     *
     * @return string
     */
    public function getLoadBlockUrl()
    {
        return $this->getUrl('quotation/quote/loadBlock');
    }

    /**
     * Retrieve url for form submiting
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('quotation/quote/save');
    }

    /**
     * Get customer selector display
     *
     * @return string
     */
    public function getCustomerSelectorDisplay()
    {
        $customerId = $this->getCustomerId();
        if ($customerId === null) {
            return 'block';
        }
        return 'none';
    }

    /**
     * Get store selector display
     *
     * @return string
     */
    public function getStoreSelectorDisplay()
    {
        $storeId = $this->getStoreId();
        $customerId = $this->getCustomerId();
        if ($customerId !== null && !$storeId) {
            return 'block';
        }
        return 'none';
    }

    /**
     * Get data selector display
     *
     * @return string
     */
    public function getDataSelectorDisplay()
    {
        $storeId = $this->getStoreId();
        $customerId = $this->getCustomerId();
        if ($customerId !== null && $storeId) {
            return 'block';
        }
        return 'none';
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuoteDataJson()
    {
        $data = [];
        $data['quote_id'] = $this->getQuote()->getId();
        $data['shipping_method_reseted'] = !(bool)$this->getQuote()->getShippingAddress()->getShippingMethod();
        return $this->_jsonEncoder->encode($data);
    }
}
