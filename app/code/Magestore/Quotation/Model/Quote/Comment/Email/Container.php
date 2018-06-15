<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model\Quote\Comment\Email;

class Container extends \Magestore\Quotation\Model\Quote\Email\Container implements \Magento\Sales\Model\Order\Email\Container\IdentityInterface
{
    /**
     * Configuration paths
     */
    const XML_PATH_NEW_ADMIN_COMMENT_EMAIL_TEMPLATE = 'quotation/email/quote_new_comment_template';

    /**
     * Return new comment template id
     *
     * @return mixed
     */
    public function getNewCommentTemplateId()
    {
        return $this->getConfigValue(self::XML_PATH_NEW_ADMIN_COMMENT_EMAIL_TEMPLATE, $this->getStore()->getStoreId());
    }
}
