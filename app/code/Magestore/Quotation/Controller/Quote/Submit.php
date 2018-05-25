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
        $address = $this->quotationCart->getQuote()->getShippingAddress();
        $shippingAddressData = $address->toArray();
        $addressForm = $this->formFactory->create(
            'customer_address',
            'customer_address_edit',
            $shippingAddressData
        );
        $addressData = $addressForm->extractData($this->getRequest());
        $attributeValues = $addressForm->compactData($addressData);

        $addressDataObject = $this->addressDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            array_merge($shippingAddressData, $attributeValues),
            \Magento\Customer\Api\Data\AddressInterface::class
        );

        $email = $this->getRequest()->getParam('email');
        $remark = $this->getRequest()->getParam('remark');
        $this->quotationCart->getQuote()->setCustomerEmail($email);
        $this->quotationCart->getQuote()->setCustomerNote($remark);
        $this->quotationCart->getQuote()->setShippingAddress($addressDataObject);
        $this->quotationCart->getQuote()->setBillingAddress($addressDataObject);
        $this->quotationCart->save();

//        $this->quotationCart->submit();

        $resultRedirect = $this->createRedirectResult();
        return $resultRedirect->setUrl($this->_url->getUrl('*/*/'));
        return $resultRedirect->setUrl($this->_url->getUrl('*/*/success'));
    }
}
