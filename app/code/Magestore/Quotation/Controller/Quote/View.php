<?php
/**
 *
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Controller\Quote;

class View extends \Magestore\Quotation\Controller\AbstractAction
{
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * View constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magestore\Quotation\Helper\Data $helper
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magestore\Quotation\Helper\Data $helper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\Registry $registry
    ){
        parent::__construct($context, $helper);
        $this->quoteFactory = $quoteFactory;
        $this->registry = $registry;
    }

    /**
     * Customer quote detail
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $quoteId = (int)$this->getRequest()->getParam('quote_id');
        if (!$quoteId) {
            $resultForward = $this->createForwardResult();
            return $resultForward->forward('noroute');
        }

        $quote = $this->quoteFactory->create()->load($quoteId);
        if ($quote->getId()) {
            $this->registry->register('current_quote', $quote);
            $resultPage = $this->createPageResult();
            $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
            if ($navigationBlock) {
                $navigationBlock->setActive('quotation/quote/history');
            }
            return $resultPage;
        }
        $resultRedirect = $this->createRedirectResult();
        return $resultRedirect->setUrl($this->_url->getUrl('*/*/history'));
    }
}
