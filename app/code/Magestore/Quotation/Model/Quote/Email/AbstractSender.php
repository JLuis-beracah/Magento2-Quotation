<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model\Quote\Email;

/**
 * Class AbstractSender
 * @package Magestore\Quotation\Model\Quote\Email
 */
abstract class AbstractSender
{
    /**
     * @var \Magestore\Quotation\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magestore\Quotation\Model\Quote\Email\SenderBuilderFactory
     */
    protected $senderBuilderFactory;

    /**
     * @var \Magestore\Quotation\Model\Quote\Email\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Sales\Model\Order\Email\Container\Template
     */
    protected $templateContainer;

    /**
     * @var \Magento\Sales\Model\Order\Email\Container\IdentityInterface
     */
    protected $identityContainer;


    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * AbstractSender constructor.
     * @param \Magestore\Quotation\Helper\Data $helper
     * @param \Magestore\Quotation\Model\Quote\Email\TransportBuilder $transportBuilder
     * @param \Magestore\Quotation\Model\Quote\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Magento\Sales\Model\Order\Email\Container\Template $templateContainer
     * @param \Magento\Sales\Model\Order\Email\Container\IdentityInterface $identityContainer
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magestore\Quotation\Helper\Data $helper,
        \Magestore\Quotation\Model\Quote\Email\TransportBuilder $transportBuilder,
        \Magestore\Quotation\Model\Quote\Email\SenderBuilderFactory $senderBuilderFactory,
        \Magento\Sales\Model\Order\Email\Container\Template $templateContainer,
        \Magento\Sales\Model\Order\Email\Container\IdentityInterface $identityContainer,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->helper = $helper;
        $this->transportBuilder = $transportBuilder;
        $this->senderBuilderFactory = $senderBuilderFactory;
        $this->templateContainer = $templateContainer;
        $this->identityContainer = $identityContainer;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
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
                'transportBuilder' => $this->transportBuilder
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
     * @return string
     */
    protected function getCustomerName(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        if ($quote->getCustomerIsGuest()) {
            $customerName = (string)__('Guest');
            $address = $quote->getShippingAddress();
            if($address){
                $customerName = $address->getFirstname() . ' ' . $address->getLastname();
            }
        } else {
            $customerName = $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname();
        }
        return $customerName;
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
    public function getQuoteDetailUrl(\Magento\Quote\Api\Data\CartInterface $quote){
        $url = $this->helper->getUrl("quotation/quote/view", [
            'quote_id' => $quote->getEntityId()
        ]);
        $urlPaths = explode("?SID", $url);
        return $urlPaths[0];
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return $this
     */
    public function addAdditionalRecipientEmails(\Magento\Quote\Api\Data\CartInterface $quote){
        if(!empty($quote->getAdditionalRecipientEmails())){
            $additionalEmails = $quote->getAdditionalRecipientEmails();
            if(!empty($additionalEmails)){
                foreach (explode(',', $additionalEmails) as $email){
                    $this->identityContainer->addEmailCopyTo($email);
                }
            }
        }
        return $this;
    }
}
