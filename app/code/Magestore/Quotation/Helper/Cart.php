<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Helper;

/**
 * Class Cart
 * @package Magestore\Quotation\Helper
 */
class Cart extends \Magento\Checkout\Helper\Cart
{
    /**
     * Path to controller to delete item from cart
     */
    const DELETE_URL = 'quotation/quote/delete';

    /**
     * @var \Magestore\Quotation\Model\Cart
     */
    protected $quotationCart;

    /**
     * @var \Magestore\Quotation\Model\Session
     */
    protected $quotationSession;

    /**
     * Cart constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Checkout\Model\Cart $checkoutCart
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magestore\Quotation\Model\Cart $quotationCart
     * @param \Magestore\Quotation\Model\Session $quotationSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magestore\Quotation\Model\Cart $quotationCart,
        \Magestore\Quotation\Model\Session $quotationSession
    ) {
        parent::__construct($context, $checkoutCart, $checkoutSession);
        $this->quotationCart = $quotationCart;
        $this->quotationSession = $quotationSession;
    }

    /**
     * Retrieve cart instance
     *
     * @return \Magento\Checkout\Model\Cart
     * @codeCoverageIgnore
     */
    public function getCart()
    {
        return $this->quotationCart;
    }

    /**
     * Retrieve current quote instance
     *
     * @return \Magento\Quote\Model\Quote
     * @codeCoverageIgnore
     */
    public function getQuote()
    {
        return $this->quotationSession->getQuote();
    }
}
