<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Cart\Info\Address;

/**
 * Class Shipping
 * @package Magestore\Quotation\Block\Cart\Info\Address
 */
class Shipping extends \Magestore\Quotation\Block\Cart\Info\Address
{
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
     * @return string
     */
    public function getAddressPrefix(){
        return "shipping";
    }

    /**
     * @return string
     */
    public function getHeaderTitle(){
        return __('Shipping Details');
    }

}
