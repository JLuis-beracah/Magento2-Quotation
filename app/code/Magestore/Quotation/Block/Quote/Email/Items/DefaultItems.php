<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Quote\Email\Items;

use Magestore\Quotation\Model\Source\Quote\Status as QuoteStatus;

class DefaultItems extends \Magento\Sales\Block\Order\Email\Items\DefaultItems
{
    /**
     * DefaultItems constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magestore\Quotation\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magestore\Quotation\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * Retrieve current quote model instance
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->getItem()->getQuote();
    }

    /**
     * @return array
     */
    public function getItemOptions()
    {
        $result = [];
        $item = $this->getItem();
        $options = $item->getProductOrderOptions();
        if (!$options) {
            $options = $item->getProduct()->getTypeInstance()->getOrderOptions($item->getProduct());
        }
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        return $result;
    }

    /**
     * @param mixed $item
     * @return mixed
     */
    public function getSku($item)
    {
        if ($item->getOptionByCode('simple_sku')) {
            return $item->getOptionByCode('simple_sku');
        } else {
            return $item->getSku();
        }
    }

    /**
     * @return bool
     */
    public function canShowPrice(){
        $item = $this->getItem();
        return(
            ($item->getRequestStatus() == QuoteStatus::STATUS_PROCESSED) ||
            ($item->getRequestStatus() == QuoteStatus::STATUS_ORDERED)
        )?true:false;
    }

    /**
     * @param $price
     * @return \Magento\Framework\Phrase|mixed
     */
    public function getPrice($price){
        $item = $this->getItem();
        $itemRequestStatus = $item->getRequestStatus();
        $statusLabels = QuoteStatus::getOptionArray();
        $label = __('Processing');
        if($this->canShowPrice()){
            $label = $this->helper->formatQuotePrice($this->getQuote(),$price);
        }elseif($itemRequestStatus && isset($statusLabels[$itemRequestStatus])){
            $label = $statusLabels[$itemRequestStatus];
        }
        return $label;
    }
}
