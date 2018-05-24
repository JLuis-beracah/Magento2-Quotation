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

    public function getSubmitUrl(){
        return $this->getUrl("quotation/quote/submit");
    }

    public function getSubmitButtonTitle(){
        return __("Submit Quote Request");
    }
}
