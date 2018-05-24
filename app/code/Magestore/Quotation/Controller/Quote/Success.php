<?php
/**
 *
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Quotation\Controller\Quote;

class Success extends \Magestore\Quotation\Controller\AbstractAction
{
    /**
     * @var \Magestore\Quotation\Model\Session
     */
    protected $quotationSession;

    /**
     * Success constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magestore\Quotation\Helper\Data $helper
     * @param \Magestore\Quotation\Model\Session $quotationSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magestore\Quotation\Helper\Data $helper,
        \Magestore\Quotation\Model\Session $quotationSession
    ){
        parent::__construct($context, $helper);
        $this->quotationSession = $quotationSession;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $quote = $this->quotationSession->getLastRealQuote();
        if(!$quote){
            $resultRedirect = $this->createRedirectResult();
            return $resultRedirect->setUrl($this->_url->getUrl('*/*'));
        }
        $resultPage = $this->createPageResult();
        return $resultPage;
    }
}
