<?php
/**
 *
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Controller\Adminhtml\Quote;

class Address extends \Magestore\Quotation\Controller\Adminhtml\AbstractAction
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $addressId = $this->getRequest()->getParam('address_id');
        $address = $this->_objectManager->create(\Magento\Quote\Model\Quote\Address::class)->load($addressId);
        if ($address->getId()) {
            $this->registry->register('order_address', $address);
            $this->registry->register('current_quote_address', $address);
            $resultPage = $this->createPageResult();
            $addressFormContainer = $resultPage->getLayout()->getBlock('quotation_quote_address.form.container');
            if ($addressFormContainer) {
                $addressFormContainer->getChildBlock('form')->setDisplayVatValidationButton(false);
            }
            return $resultPage;
        } else {
            return $this->createRedirectResult()->setPath('quotation/*/');
        }
    }
}
