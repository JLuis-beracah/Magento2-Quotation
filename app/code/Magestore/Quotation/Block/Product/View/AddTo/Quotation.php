<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Quotation\Block\Product\View\AddTo;

/**
 * Class Quotation
 * @package Magestore\Quotation\Block\Product\View\AddTo
 */
class Quotation extends \Magento\Catalog\Block\Product\View
{

    /**
     * Return quotation url
     *
     * @return string
     * @since 101.0.1
     */
    public function getAddToQuotationUrl()
    {
        return $this->getUrl("quotation/quote/add");
    }
}
