<?php
/**
 *
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Controller\Quote;

class Submit extends \Magestore\Quotation\Controller\AbstractAction
{
    /**
     * @var \Magestore\Quotation\Model\Cart
     */
    protected $quotationCart;

    /**
     * Submit constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magestore\Quotation\Helper\Data $helper
     * @param \Magestore\Quotation\Model\Cart $quotationCart
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magestore\Quotation\Helper\Data $helper,
        \Magestore\Quotation\Model\Cart $quotationCart
    ){
        parent::__construct($context, $helper);
        $this->quotationCart = $quotationCart;
    }

    /**
     * Customer quote detail
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $quote = $this->quotationCart->getQuote();
        if (!$quote || ($quote && !$quote->getId())) {
            $resultForward = $this->createForwardResult();
            return $resultForward->forward('noroute');
        }
        $this->quotationCart->submit();

        $resultRedirect = $this->createRedirectResult();
        return $resultRedirect->setUrl($this->_url->getUrl('*/*/success'));
    }
}
