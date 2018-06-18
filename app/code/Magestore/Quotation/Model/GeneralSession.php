<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model;

/**
 * Class GeneralSession
 * @package Magestore\Quotation\Model
 */
class GeneralSession extends \Magento\Backend\Model\Session\Quote
{
    /**
     * @return $this
     */
    public function reset(){
        $this->storage->unsetData("new_quotation_quote_id");
        $this->storage->unsetData("new_quotation_customer_id");
        $this->storage->unsetData("new_quotation_currencry_id");
        $this->storage->unsetData("new_quotation_store_id");
        $this->storage->unsetData("new_quotation_quote_id");
        return $this;
    }
}
