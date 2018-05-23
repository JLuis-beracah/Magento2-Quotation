<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block;

use Magento\Quote\Model\Quote;

/**
 * Class AbstractCart
 * @package Magestore\Quotation\Block
 */
class AbstractCart extends \Magento\Checkout\Block\Cart\AbstractCart
{
    /**
     * @var \Magestore\Quotation\Model\Session
     */
    protected $quotationSession;

    /**
     * @var Quote|null
     */
    protected $_quotation_quote = null;

    /**
     * AbstractCart constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magestore\Quotation\Model\Session $quotationSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magestore\Quotation\Model\Session $quotationSession,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->quotationSession = $quotationSession;
    }

    /**
     * Get active quote
     *
     * @return Quote
     */
    public function getQuote()
    {
        if (null === $this->_quotation_quote) {
            $this->_quotation_quote = $this->quotationSession->getQuote();
        }
        return $this->_quotation_quote;
    }

}
