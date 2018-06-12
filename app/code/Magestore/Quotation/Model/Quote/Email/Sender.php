<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model\Quote\Email;

use Magento\Sales\Model\Order\Address\Renderer as OrderAddressRenderer;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Quote\Model\Quote\Address\ToOrderAddress as ConvertQuoteAddressToOrderAddress;
use Magento\Framework\Event\ManagerInterface;

/**
 * Class Sender
 * @package Magestore\Quotation\Model\Quote
 */
class Sender
{
    /**
     * @var \Magestore\Quotation\Helper\Data
     */
    protected $helper;

    /**
     * @var SenderBuilderFactory
     */
    protected $senderBuilderFactory;

    /**
     * @var OrderAddressRenderer
     */
    protected $addressRenderer;

    /**
     * @var Template
     */
    protected $templateContainer;

    /**
     * @var IdentityInterface
     */
    protected $identityContainer;

    /**
     * @var ConvertQuoteAddressToOrderAddress
     */
    protected $toOrderAddress;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Application Event Dispatcher
     *
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * Sender constructor.
     * @param \Magestore\Quotation\Helper\Data $helper
     * @param SenderBuilderFactory $senderBuilderFactory
     * @param OrderAddressRenderer $addressRenderer
     * @param Template $templateContainer
     * @param Container $identityContainer
     * @param \Psr\Log\LoggerInterface $logger
     * @param ConvertQuoteAddressToOrderAddress $toOrderAddress
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        \Magestore\Quotation\Helper\Data $helper,
        \Magestore\Quotation\Model\Quote\Email\SenderBuilderFactory $senderBuilderFactory,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Sales\Model\Order\Email\Container\Template $templateContainer,
        \Magestore\Quotation\Model\Quote\Email\Container $identityContainer,
        \Psr\Log\LoggerInterface $logger,
        ConvertQuoteAddressToOrderAddress $toOrderAddress,
        ManagerInterface $eventManager
    ) {
        $this->helper = $helper;
        $this->senderBuilderFactory = $senderBuilderFactory;
        $this->addressRenderer = $addressRenderer;
        $this->templateContainer = $templateContainer;
        $this->identityContainer = $identityContainer;
        $this->logger = $logger;
        $this->toOrderAddress = $toOrderAddress;
        $this->eventManager = $eventManager;
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
            $fileName = $this->getQuotePdfFileName($quote);
            $sender->getTransportBuilder()->setPdfFilename($fileName);
            $sender->getTransportBuilder()->setAttachPdf(true);
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
        $transport = [
            'quote' => $quote,
            'billing' => $quote->getBillingAddress(),
            'store' => $quote->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($quote),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($quote),
            'customer_name' => $this->getCustomerName($quote),
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
            $customerName = (string)__('Guest');
            $address = $quote->getShippingAddress();
            if($address){
                $customerName = $address->getFirstname() . ' ' . $address->getLastname();
            }
        } else {
            $templateId = $this->identityContainer->getTemplateId();
            $customerName = $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname();
        }
        $this->identityContainer->setCustomerName($customerName);
        $this->identityContainer->setCustomerEmail($quote->getCustomerEmail());
        $this->templateContainer->setTemplateId($templateId);
    }

    /**
     * @return Sender
     */
    protected function getSender()
    {
        return $this->senderBuilderFactory->create(
            [
                'templateContainer' => $this->templateContainer,
                'identityContainer' => $this->identityContainer,
            ]
        );
    }

    /**
     * @return array
     */
    protected function getTemplateOptions()
    {
        return [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $this->identityContainer->getStore()->getStoreId()
        ];
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
    protected function getCustomerName(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        if ($quote->getCustomerFirstname()) {
            $customerName = $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname();
        } else {
            $customerName = (string)__('Guest');
            $address = $quote->getShippingAddress();
            if($address){
                $customerName = $address->getFirstname() . ' ' . $address->getLastname();
            }
        }
        return $customerName;
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
}
