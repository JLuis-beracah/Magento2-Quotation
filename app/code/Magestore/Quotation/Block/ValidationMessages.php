<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block;

use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;

/**
 * Class ValidationMessages
 * @package Magestore\Quotation\Block
 */
class ValidationMessages extends \Magento\Framework\View\Element\Messages
{
    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $quotationSession;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $currency;

    /**
     * ValidationMessages constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Message\Factory $messageFactory
     * @param \Magento\Framework\Message\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param InterpretationStrategyInterface $interpretationStrategy
     * @param \Magestore\Quotation\Model\Session $quotationSession
     * @param \Magento\Framework\Locale\CurrencyInterface $currency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Message\Factory $messageFactory,
        \Magento\Framework\Message\CollectionFactory $collectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        InterpretationStrategyInterface $interpretationStrategy,
        \Magestore\Quotation\Model\Session $quotationSession,
        \Magento\Framework\Locale\CurrencyInterface $currency,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $messageFactory,
            $collectionFactory,
            $messageManager,
            $interpretationStrategy,
            $data
        );
        $this->quotationSession = $quotationSession;
        $this->currency = $currency;
    }

    /**
     * Add quote messages
     *
     * @return void
     */
    protected function addQuoteMessages()
    {
        // Compose array of messages to add
        $messages = [];
        /** @var \Magento\Framework\Message\MessageInterface $message */
        foreach ($this->quotationSession->getQuote()->getMessages() as $message) {
            if ($message) {
                // Escape HTML entities in quote message to prevent XSS
                $message->setText($this->escapeHtml($message->getText()));
                $messages[] = $message;
            }
        }

        if ($messages) {
            $this->messageManager->addUniqueMessages($messages);
        }
    }
}
