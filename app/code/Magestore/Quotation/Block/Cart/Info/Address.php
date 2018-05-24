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
        parent::_prepareLayout();

        // Init address object
        if ($addressId = $this->getRequest()->getParam('id')) {
            try {
                $this->_address = $this->_addressRepository->getById($addressId);
                if ($this->_address->getCustomerId() != $this->_customerSession->getCustomerId()) {
                    $this->_address = null;
                }
            } catch (NoSuchEntityException $e) {
                $this->_address = null;
            }
        }

        if ($this->_address === null || !$this->_address->getId()) {
            $this->_address = $this->addressDataFactory->create();
            $customer = $this->getCustomer();
            $this->_address->setPrefix($customer->getPrefix());
            $this->_address->setFirstname($customer->getFirstname());
            $this->_address->setMiddlename($customer->getMiddlename());
            $this->_address->setLastname($customer->getLastname());
            $this->_address->setSuffix($customer->getSuffix());
        }

        $this->pageConfig->getTitle()->set($this->getTitle());

        if ($postedData = $this->_customerSession->getAddressFormData(true)) {
            $postedData['region'] = [
                'region' => $postedData['region'] ?? null,
            ];
            if (!empty($postedData['region_id'])) {
                $postedData['region']['region_id'] = $postedData['region_id'];
            }
            $this->dataObjectHelper->populateWithArray(
                $this->_address,
                $postedData,
                \Magento\Customer\Api\Data\AddressInterface::class
            );
        }

        return $this;
    }

    /**
     * Retrieve the Customer Data using the customer Id from the customer session.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        return $this->currentCustomer->getCustomer();
    }

}
