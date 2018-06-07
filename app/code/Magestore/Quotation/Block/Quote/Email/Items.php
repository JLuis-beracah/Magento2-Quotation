<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Quote\Email;

use Magestore\Quotation\Model\Source\Quote\Status as QuoteStatus;

/**
 * Class Items
 * @package Magestore\Quotation\Block\Quote\Email
 */
class Items extends \Magento\Sales\Block\Order\Email\Items
{
    /**
     * @return bool
     */
    public function canShowPrice(){
        $quote = $this->getQuote();
        return(
            ($quote->getRequestStatus() == QuoteStatus::STATUS_PROCESSED) ||
            ($quote->getRequestStatus() == QuoteStatus::STATUS_ORDERED)
        )?true:false;
    }
}
