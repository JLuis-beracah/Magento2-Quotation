<?php
/**
 *
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Controller\Quote;

use Magento\Customer\Api\Data\RegionInterface;
use Magento\Directory\Model\RegionFactory;

class Submit extends \Magestore\Quotation\Controller\AbstractAction
{
    /**
     * @var \Magestore\Quotation\Model\Cart
     */
    protected $quotationCart;

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Quote\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Customer\Api\Data\RegionInterfaceFactory
     */
    protected $regionDataFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;

    /**
     * Submit constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magestore\Quotation\Helper\Data $helper
     * @param \Magestore\Quotation\Model\Cart $quotationCart
     * @param \Magento\Customer\Model\Metadata\FormFactory $formFactory
     * @param \Magento\Quote\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory
     * @param RegionFactory $regionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magestore\Quotation\Helper\Data $helper,
        \Magestore\Quotation\Model\Cart $quotationCart,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Quote\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory
    ){
        parent::__construct($context, $helper);
        $this->quotationCart = $quotationCart;
        $this->formFactory = $formFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->regionDataFactory = $regionDataFactory;
        $this->regionFactory = $regionFactory;
    }

    /**
     * Customer quote detail
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $quote = $this->quotationCart->getQuote();
        if (!$quote || ($quote && !$quote->getId())) {
            $resultForward = $this->createForwardResult();
            return $resultForward->forward('noroute');
        }
        $billing = $this->getRequest()->getParam("billing");
        $shipping = $this->getRequest()->getParam("shipping");
        $email = $this->getRequest()->getParam('email');
        $remark = $this->getRequest()->getParam('remark');
        $billingAddressDataObject = $this->getAddressDataObject($billing, "billing");
        if(!$quote->isVirtual()){
            if(isset($shipping['same_as_billing']) && ($shipping['same_as_billing'] == "1")){
                $shippingAddressDataObject = $billingAddressDataObject;
                $shippingAddressDataObject->setSameAsBilling(1);
            }else{
                $shippingAddressDataObject = $this->getAddressDataObject($shipping, "shipping");
                $shippingAddressDataObject->setSameAsBilling(0);
            }
        }
        $customerSession = $this->quotationCart->getCustomerSession();
        $customerIsGuest = ($customerSession->isLoggedIn())?false:true;
        $this->quotationCart->getQuote()->setCustomerIsGuest($customerIsGuest);
        $this->quotationCart->getQuote()->setCustomerEmail($email);
        $this->quotationCart->getQuote()->setCustomerNote($remark);
        $this->quotationCart->getQuote()->setBillingAddress($billingAddressDataObject);
        if(!$quote->isVirtual()){
            $this->quotationCart->getQuote()->setShippingAddress($shippingAddressDataObject);
        }
        $this->quotationCart->save();
        if($remark){
            $this->quotationCart->addComment($remark);
        }
        $this->quotationCart->submit();
        $resultRedirect = $this->createRedirectResult();
        return $resultRedirect->setUrl($this->_url->getUrl('*/*/success'));
    }

    /**
     * @param $addressSubmitData
     * @param $type
     * @return mixed
     */
    protected function getAddressDataObject($addressSubmitData, $type){
        if($type == "shipping"){
            $address = $this->quotationCart->getQuote()->getShippingAddress();
        }else{
            $address = $this->quotationCart->getQuote()->getBillingAddress();
        }
        $addressData = $address->toArray();
        $addressForm = $this->formFactory->create(
            'customer_address',
            'customer_address_edit',
            $addressData
        );
        $request = $this->getRequest();
        $request->setParams($addressSubmitData);
        $addressData = $addressForm->extractData($request);
        $attributeValues = $addressForm->compactData($addressData);

        $addressDataObject = $this->addressDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            array_merge($addressData, $attributeValues),
            \Magento\Customer\Api\Data\AddressInterface::class
        );
        return $addressDataObject;
    }
}
