<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model\Quote\Email;

use Magento\Quote\Model\Quote\Address\ToOrderAddress as ConvertQuoteAddressToOrderAddress;

/**
 * Class Sender
 * @package Magestore\Quotation\Model\Quote
 */
class Sender extends \Magestore\Quotation\Model\Quote\Email\AbstractSender
{
    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    protected $addressRenderer;

    /**
     * @var ConvertQuoteAddressToOrderAddress
     */
    protected $toOrderAddress;

    /**
     * Sender constructor.
     * @param \Magestore\Quotation\Helper\Data $helper
     * @param \Magestore\Quotation\Model\Quote\Email\TransportBuilder $transportBuilder
     * @param \Magestore\Quotation\Model\Quote\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Magento\Sales\Model\Order\Email\Container\Template $templateContainer
     * @param \Magestore\Quotation\Model\Quote\Email\Container $identityContainer
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param ConvertQuoteAddressToOrderAddress $toOrderAddress
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     */
    public function __construct(
        \Magestore\Quotation\Helper\Data $helper,
        \Magestore\Quotation\Model\Quote\Email\TransportBuilder $transportBuilder,
        \Magestore\Quotation\Model\Quote\Email\SenderBuilderFactory $senderBuilderFactory,
        \Magento\Sales\Model\Order\Email\Container\Template $templateContainer,
        \Magestore\Quotation\Model\Quote\Email\Container $identityContainer,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        ConvertQuoteAddressToOrderAddress $toOrderAddress,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
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
        $this->addressRenderer = $addressRenderer;
        $this->toOrderAddress = $toOrderAddress;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    function send(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $this->identityContainer->setStore($quote->getStore());
        if (!$this->identityContainer->isEnabled()) {
            return false;
        }
        $this->prepareTemplate($quote);

        /** @var SenderBuilder $sender */
        $sender = $this->getSender();

        try {
            $attachPdf = $this->isAttachQuotationAsPdf($quote->getStore()->getCode());
            if($attachPdf){
                $fileName = $this->getQuotePdfFileName($quote);
                $sender->getTransportBuilder()->setPdfFilename($fileName);
                $sender->getTransportBuilder()->setAttachPdf(true);
            }
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
    protected function prepareTemplate(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $customerName = $this->getCustomerName($quote);
        $transport = [
            'quote' => $quote,
            'billing' => $quote->getBillingAddress(),
            'store' => $quote->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($quote),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($quote),
            'customer_name' => $customerName,
            'created_at_formated' => $this->getRequestedDateFormated($quote),
            'checkout_url' => $this->getCheckoutUrl($quote)
        ];
        $transport = new \Magento\Framework\DataObject($transport);

        $this->eventManager->dispatch(
            'email_quote_set_template_vars_before',
            ['sender' => $this, 'transport' => $transport]
        );

        $this->templateContainer->setTemplateVars($transport->getData());

        $this->templateContainer->setTemplateOptions($this->getTemplateOptions());

        if ($quote->getCustomerIsGuest()) {
            $templateId = $this->identityContainer->getGuestTemplateId();
        } else {
            $templateId = $this->identityContainer->getTemplateId();
        }
        $this->identityContainer->setCustomerName($customerName);
        $this->identityContainer->setCustomerEmail($quote->getCustomerEmail());
        $this->templateContainer->setTemplateId($templateId);
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return null|string
     */
    protected function getFormattedShippingAddress(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $address = $this->toOrderAddress->convert($quote->getShippingAddress());
        return $quote->getIsVirtual()
            ? null
            : $this->addressRenderer->format($address, 'html');
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return null|string
     */
    protected function getFormattedBillingAddress(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $address = $this->toOrderAddress->convert($quote->getBillingAddress());
        return $this->addressRenderer->format($address, 'html');
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return string
     */
    protected function getRequestedDateFormated(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        return $quote->getCreatedAt();
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return string
     */
    public function getCheckoutUrl(\Magento\Quote\Api\Data\CartInterface $quote){
        $url = $this->helper->getUrl("quotation/quote/checkout", [
            'id' => $quote->getEntityId()
        ]);
        $urlPaths = explode("?SID", $url);
        return $urlPaths[0];
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return string
     */
    public function getQuotePdfFileName(\Magento\Quote\Api\Data\CartInterface  $quote){
        return __("Quotation_#%1.pdf", $quote->getId())->__toString();
    }

    /**
     * @param null $storeCode
     * @return bool
     */
    public function isAttachQuotationAsPdf($storeCode = null){
        $attachPdf = $this->helper->getStoreConfig("quotation/email/send_pdf", $storeCode);
        return ($attachPdf)?true:false;
    }
}
