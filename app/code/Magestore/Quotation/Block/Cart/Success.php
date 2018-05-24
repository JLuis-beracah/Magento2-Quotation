<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Cart;

use Magento\Customer\Model\Context;
use Magento\Quote\Model\Quote;

/**
 * Class Success
 * @package Magestore\Quotation\Block\Cart
 */
class Success extends \Magento\Checkout\Block\Onepage\Success
{

    /**
     * @var \Magestore\Quotation\Model\Cart
     */
    protected $quotationCart;

    /**
     * @var \Magestore\Quotation\Model\Session
     */
    protected $quotationSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Success constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magestore\Quotation\Model\Cart $quotationCart
     * @param \Magestore\Quotation\Model\Session $quotationSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magestore\Quotation\Model\Cart $quotationCart,
        \Magestore\Quotation\Model\Session $quotationSession,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
        $this->quotationCart = $quotationCart;
        $this->quotationSession = $quotationSession;
        $this->customerSession = $customerSession;
    }

    /**
     * Prepares block data
     *
     * @return void
     */
    protected function prepareBlockData()
    {
        $quote = $this->quotationSession->getLastRealQuote();
        $this->addData(
            [
                'view_quote_url' => $this->getUrl(
                    'quotation/quote/view/',
                    ['quote_id' => $quote->getEntityId()]
                ),
                'can_view_quote'  => $this->canViewQuote($quote),
                'quote_id'  => $quote->getEntityId()
            ]
        );
        $this->quotationCart->reset();
    }

    /**
     * @param Quote $quote
     * @return bool
     */
    protected function canViewQuote(Quote $quote)
    {
        $canView = false;
        if($this->customerSession->isLoggedIn()){
            $customer = $this->customerSession->getCustomer();
            if($customer->getId() == $quote->getCustomerId()){
                $canView = true;
            }
        }
        return $canView;
    }

}
