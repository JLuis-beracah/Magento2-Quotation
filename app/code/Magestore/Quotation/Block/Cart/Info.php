<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Quotation\Block\Cart;

/**
 * Class Info
 * @package Magestore\Quotation\Block\Cart
 */
class Info extends \Magestore\Quotation\Block\Cart
{

    /**
     * Prepare the layout of the address edit block.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $quote =  $this->getQuote();
        if($quote && $quote->getCustomerId()){
            $customer = $quote->getCustomer();
            if(!$this->getEmail()){
                $this->setEmail($customer->getEmail());
            }

            if(!$this->getCustomerNote()){
                $this->setCustomerNote($quote->getCustomerNote());
            }
        }
        return $this;
    }

    public function getSubmitUrl(){
        return $this->getUrl("quotation/quote/submit");
    }

    public function getSubmitButtonTitle(){
        return __("Submit Quote Request");
    }
}
