<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Cart\Item\Renderer\Actions;

use Magento\Checkout\Helper\Cart;
use Magento\Framework\View\Element\Template;

/**
 * @api
 */
class Remove extends \Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic
{
    /**
     * @var Cart
     */
    protected $cartHelper;

    /**
     * @param Template\Context $context
     * @param Cart $cartHelper
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        Template\Context $context,
        Cart $cartHelper,
        array $data = []
    ) {
        $this->cartHelper = $cartHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get delete item POST JSON
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getDeletePostJson()
    {
        $url = $this->getUrl('quotation/quote/delete');
        $data = ['id' => $this->getItem()->getId()];
        if (!$this->_request->isAjax()) {
            $data[\Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED] = $this->cartHelper->getCurrentBase64Url();
        }
        return json_encode(['action' => $url, 'data' => $data]);
    }
}
