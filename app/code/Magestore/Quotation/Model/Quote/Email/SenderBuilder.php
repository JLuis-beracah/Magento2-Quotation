<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model\Quote\Email;

class SenderBuilder extends \Magento\Sales\Model\Order\Email\SenderBuilder
{
    /**
     * SenderBuilder constructor.
     * @param \Magento\Sales\Model\Order\Email\Container\Template $templateContainer
     * @param \Magento\Sales\Model\Order\Email\Container\IdentityInterface $identityContainer
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param TransportBuilder $magestoreTransportBuilder
     */
    public function __construct(
        \Magento\Sales\Model\Order\Email\Container\Template $templateContainer,
        \Magento\Sales\Model\Order\Email\Container\IdentityInterface $identityContainer,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magestore\Quotation\Model\Quote\Email\TransportBuilder $magestoreTransportBuilder
    ) {
        parent::__construct($templateContainer, $identityContainer, $transportBuilder);
        $this->transportBuilder = $magestoreTransportBuilder;
    }

    /**
     * @return TransportBuilder
     */
    public function getTransportBuilder(){
        return $this->transportBuilder;
    }
}
