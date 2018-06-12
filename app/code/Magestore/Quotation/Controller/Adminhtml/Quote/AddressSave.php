<?php
/**
 *
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Controller\Adminhtml\Quote;

/**
 * Class AddressSave
 * @package Magestore\Quotation\Controller\Adminhtml\Quote
 */
class AddressSave extends \Magestore\Quotation\Controller\Adminhtml\Quote\QuoteAbstract
{
    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;

    /**
     * AddressSave constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magestore\Quotation\Helper\Data $helper
     * @param \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement
     * @param \Magestore\Quotation\Model\BackendCart $backendCart
     * @param \Magestore\Quotation\Model\BackendSession $backendSession
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $initializationHelper
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magestore\Quotation\Helper\Data $helper,
        \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement,
        \Magestore\Quotation\Model\BackendCart $backendCart,
        \Magestore\Quotation\Model\BackendSession $backendSession,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $initializationHelper,
        \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Directory\Model\RegionFactory $regionFactory
    ){
        parent::__construct($context, $helper, $quotationManagement, $backendCart, $backendSession, $registry, $productBuilder, $initializationHelper, $productTypeManager, $productRepository);
        $this->regionFactory = $regionFactory;
    }

    /**
     * Save order address
     *
     * @return Redirect
     */
    public function execute()
    {
        $addressId = $this->getRequest()->getParam('address_id');
        /** @var $address OrderAddressInterface|AddressModel */
        $address = $this->_objectManager->create(
            \Magento\Quote\Api\Data\AddressInterface::class
        )->load($addressId);
        $data = $this->getRequest()->getPostValue();
        $data = $this->updateRegionData($data);
        $resultRedirect = $this->createRedirectResult();
        if ($data && $address->getId()) {
            $address->addData($data);
            try {
                $address->save();
                $this->_eventManager->dispatch(
                    'admin_quotation_quote_address_update',
                    [
                        'quote_id' => $address->getQuoteId()
                    ]
                );
                $this->messageManager->addSuccess(__('You updated the quote address.'));
                return $resultRedirect->setPath('quotation/*/edit', ['id' => $address->getQuoteId()]);
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t update the quote address right now.'));
            }
            return $resultRedirect->setPath('quotation/*/address', ['address_id' => $address->getId()]);
        } else {
            return $resultRedirect->setPath('quotation/*/');
        }
    }

    /**
     * Update region data
     *
     * @param array $attributeValues
     * @return array
     */
    private function updateRegionData($attributeValues)
    {
        if (!empty($attributeValues['region_id'])) {
            $newRegion = $this->regionFactory->create()->load($attributeValues['region_id']);
            $attributeValues['region_code'] = $newRegion->getCode();
            $attributeValues['region'] = $newRegion->getDefaultName();
        }
        return $attributeValues;
    }
}
