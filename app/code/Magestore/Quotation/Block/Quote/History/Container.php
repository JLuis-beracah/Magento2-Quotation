<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Quote\History;

/**
 * Class Container
 * @package Magestore\Quotation\Block\Quote\History
 */
class Container extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Quote\Api\Data\CartInterface
     */
    private $quote;

    /**
     * Set quote
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return $this
     * @since 100.1.1
     */
    public function setOrder(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $this->quote = $quote;
        return $this;
    }

    /**
     * Get quote
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    private function getQuote()
    {
        return $this->quote;
    }

    /**
     * Here we set an quote for children during retrieving their HTML
     *
     * @param string $alias
     * @param bool $useCache
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 100.1.1
     */
    public function getChildHtml($alias = '', $useCache = false)
    {
        $layout = $this->getLayout();
        if ($layout) {
            $name = $this->getNameInLayout();
            foreach ($layout->getChildBlocks($name) as $child) {
                $child->setQuote($this->getQuote());
            }
        }
        return parent::getChildHtml($alias, $useCache);
    }
}
