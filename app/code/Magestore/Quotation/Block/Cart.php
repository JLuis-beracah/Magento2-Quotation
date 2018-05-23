<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block;

/**
 * Class Cart
 * @package Magestore\Quotation\Block
 */
class Cart extends \Magestore\Quotation\Block\AbstractCart
{

    /**
     * @return bool
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsVirtual()
    {
        return $this->getQuote()->isVirtual();
    }

}
