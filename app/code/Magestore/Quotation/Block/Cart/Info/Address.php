<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Cart\Info;

use Magento\Customer\Model\AttributeChecker;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Address
 * @package Magestore\Quotation\Block\Cart\Info
 */
class Address extends \Magento\Customer\Block\Address\Edit
{
    /**
     * @var \Magestore\Quotation\Model\Cart
     */
    protected $quotationCart;

    /**
     * Address constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magestore\Quotation\Model\Cart $quotationCart
     * @param array $data
     * @param AttributeChecker|null $attributeChecker
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magestore\Quotation\Model\Cart $quotationCart,
        array $data = [],
        \Magento\Customer\Model\AttributeChecker $attributeChecker = null
    ) {
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $customerSession,
            $addressRepository,
            $addressDataFactory,
            $currentCustomer,
            $dataObjectHelper,
            $data,
            $attributeChecker
        );
        $this->quotationCart = $quotationCart;
    }

    /**
     * Prepare the layout of the address edit block.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->_address === null || !$this->_address->getId()) {
            $this->_address = $this->getQuote()->getShippingAddress();
            $customerId = $this->getCurrentCustomerId();
            if($customerId){
                $customer = $this->getCustomer();
                if(!$this->_address->getPrefix()){
                    $this->_address->setPrefix($customer->getPrefix());
                }
                if(!$this->_address->getFirstname()){
                    $this->_address->setFirstname($customer->getFirstname());
                }
                if(!$this->_address->getMiddlename()){
                    $this->_address->setMiddlename($customer->getMiddlename());
                }
                if(!$this->_address->getLastname()){
                    $this->_address->setLastname($customer->getLastname());
                }
                if(!$this->_address->getSuffix()){
                    $this->_address->setSuffix($customer->getSuffix());
                }
                if(!$this->_address->getEmail()){
                    $this->_address->setEmail($customer->getEmail());
                }
            }
        }
        return $this;
    }

    /**
     * @return \Magento\Quote\Model\Quote|mixed
     */
    public function getQuote(){
        return $this->quotationCart->getQuote();
    }

    /**
     * @return int|null
     */
    public function getCurrentCustomerId()
    {
        return $this->currentCustomer->getCustomerId();
    }

    /**
     * @return string
     */
    public function getEmail(){
        $quote = $this->getQuote();
        return $quote->getCustomerEmail();
    }

    /**
     * @return string
     */
    public function getRemark(){
        $quote = $this->getQuote();
        return $quote->getCustomerNote();
    }

    /**
     * Return the name of the region for the address being edited.
     *
     * @return string region name
     */
    public function getRegion()
    {
        return $this->getAddress()->getRegion();
    }

    /**
     * Return the id of the region being edited.
     *
     * @return int region id
     */
    public function getRegionId()
    {
        return $this->getAddress()->getRegionId();
    }
}
