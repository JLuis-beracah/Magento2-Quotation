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

    /**
     * @return string
     */
    public function getAddressPrefix(){
        return "";
    }

    /**
     * @return string
     */
    public function getHeaderTitle(){
        return "";
    }

    /**
     * @param $field
     * @return string
     */
    public function getAddressFieldName($field){
        return ($this->getAddressPrefix())?$this->getAddressPrefix()."[$field]":$field;
    }

    /**
     * @return string
     */
    public function getContainerHtmlId(){
        return $this->getAddressPrefix()."_address_container";
    }

    /**
     * @return string
     */
    public function getAddressFieldId($field){
        return $this->getAddressPrefix()."_".$field;
    }

    /**
     * Generate name block html.
     *
     * @return string
     */
    public function getNameBlockHtml()
    {
        $nameBlock = $this->getLayout()
            ->createBlock(\Magento\Customer\Block\Widget\Name::class)
            ->setObject($this->getAddress());
        $nameBlock = $this->getWidget($nameBlock);
        return $nameBlock->toHtml();
    }

    /**
     * @param $class
     * @param string $template
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWidget($class, $template = ""){
        $prefix = $this->getAddressPrefix();
        if(is_string($class)){
            $widget = $this->getLayout()->createBlock($class);
        }else{
            $widget = $class;
        }
        $widget->setData('field_name_format', $prefix.'[%s]');
        $widget->setData('field_id_format', $prefix.'_%s');
        if($template){
            $widget->setPrefix($prefix);
            $widget->setTemplate($template);
        }
        return $widget;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCompanyWidget(){
        return $this->getWidget(\Magento\Customer\Block\Widget\Company::class, 'Magestore_Quotation::customer/widget/company.phtml');
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFaxWidget(){
        return $this->getWidget(\Magento\Customer\Block\Widget\Fax::class, 'Magestore_Quotation::customer/widget/fax.phtml');
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTelephoneWidget(){
        return $this->getWidget(\Magento\Customer\Block\Widget\Telephone::class, 'Magestore_Quotation::customer/widget/telephone.phtml');
    }

    /**
     * @return bool
     */
    public function isBillingAddress(){
        return false;
    }

    /**
     * @return bool
     */
    public function isVirtual(){
        return $this->getQuote()->isVirtual();
    }
}
