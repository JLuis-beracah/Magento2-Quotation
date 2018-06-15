<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model\Quote\Comment\Email;

use Magestore\Quotation\Api\Data\QuoteCommentHistoryInterface;

/**
 * Class Sender
 * @package Magestore\Quotation\Model\Quote\Comment\Email
 */
class Sender extends \Magestore\Quotation\Model\Quote\Email\AbstractSender
{
    /**
     * Sender constructor.
     * @param \Magestore\Quotation\Helper\Data $helper
     * @param \Magestore\Quotation\Model\Quote\Email\TransportBuilder $transportBuilder
     * @param \Magestore\Quotation\Model\Quote\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Magento\Sales\Model\Order\Email\Container\Template $templateContainer
     * @param \Magestore\Quotation\Model\Quote\Comment\Email\Container $identityContainer
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magestore\Quotation\Helper\Data $helper,
        \Magestore\Quotation\Model\Quote\Email\TransportBuilder $transportBuilder,
        \Magestore\Quotation\Model\Quote\Email\SenderBuilderFactory $senderBuilderFactory,
        \Magento\Sales\Model\Order\Email\Container\Template $templateContainer,
        \Magestore\Quotation\Model\Quote\Comment\Email\Container $identityContainer,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        parent::__construct(
            $helper,
            $transportBuilder,
            $senderBuilderFactory,
            $templateContainer,
            $identityContainer,
            $logger,
            $eventManager
        );
    }

    /**
     * @param QuoteCommentHistoryInterface $commentHistory
     * @return bool
     */
    function send(\Magestore\Quotation\Api\Data\QuoteCommentHistoryInterface $commentHistory)
    {
        $quote = $commentHistory->getQuote();
        $this->identityContainer->setStore($quote->getStore());
        if (!$this->identityContainer->isEnabled()) {
            return false;
        }
        $this->prepareTemplate($commentHistory);
        $sender = $this->getSender();

        try {
            $sender->send();
            $sender->sendCopyTo();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return true;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return void
     */
    protected function prepareTemplate(\Magestore\Quotation\Api\Data\QuoteCommentHistoryInterface $commentHistory)
    {
        $quote = $commentHistory->getQuote();
        $customerName = $this->getCustomerName($quote);
        $transport = [
            'quote' => $quote,
            'store' => $quote->getStore(),
            'customer_name' => $customerName,
            'comment' => $commentHistory->getComment(),
            'quote_detail_url' => $this->getQuoteDetailUrl($quote)
        ];
        $transport = new \Magento\Framework\DataObject($transport);

        $this->eventManager->dispatch(
            'email_quote_new_comment_set_template_vars_before',
            ['sender' => $this, 'transport' => $transport]
        );

        $this->templateContainer->setTemplateVars($transport->getData());
        $this->templateContainer->setTemplateOptions($this->getTemplateOptions());

        $templateId = $this->identityContainer->getNewCommentTemplateId();
        $this->identityContainer->setCustomerName($customerName);
        $this->identityContainer->setCustomerEmail($quote->getCustomerEmail());
        $this->templateContainer->setTemplateId($templateId);
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return string
     */
    public function getQuoteDetailUrl(\Magento\Quote\Api\Data\CartInterface $quote){
        $url = $this->helper->getUrl("quotation/quote/view", [
            'quote_id' => $quote->getEntityId()
        ]);
        $urlPaths = explode("?SID", $url);
        return $urlPaths[0];
    }

}
