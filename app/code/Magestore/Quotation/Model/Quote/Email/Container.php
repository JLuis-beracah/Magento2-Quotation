<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Model\Quote\Email;

class Container extends \Magento\Sales\Model\Order\Email\Container\Container implements \Magento\Sales\Model\Order\Email\Container\IdentityInterface
{
    /**
     * Configuration paths
     */
    const XML_PATH_EMAIL_COPY_METHOD = 'quotation/email/copy_method';
    const XML_PATH_EMAIL_COPY_TO = 'quotation/email/copy_to';
    const XML_PATH_EMAIL_IDENTITY = 'quotation/email/identity';
    const XML_PATH_EMAIL_TEMPLATE = 'quotation/email/quote_template';
    const XML_PATH_EMAIL_GUEST_TEMPLATE = 'quotation/email/quote_guest_template';
    const XML_PATH_EMAIL_ENABLED = 'quotation/email/enabled';

    protected $additional_emails_copy_to = [];

    /**
     * @param string $email
     * @return $this
     */
    public function addEmailCopyTo($email){
        if($email){
            if(!isset($this->additional_emails_copy_to[$email])){
                $this->additional_emails_copy_to[$email] = $email;
            }
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_EMAIL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()->getStoreId()
        );
    }

    /**
     * Return email copy_to list
     *
     * @return array|bool
     */
    public function getEmailCopyTo()
    {
        $emails = [];
        $data = $this->getConfigValue(self::XML_PATH_EMAIL_COPY_TO, $this->getStore()->getStoreId());
        if (!empty($data)) {
            $emails = explode(',', $data);
        }
        if (!empty($this->additional_emails_copy_to)) {
            $additionalEmails = array_values($this->additional_emails_copy_to);
            $emails = array_unique(array_merge($emails, $additionalEmails));
        }
        if(!empty($emails)){
            return $emails;
        }
        return false;
    }

    /**
     * Return copy method
     *
     * @return mixed
     */
    public function getCopyMethod()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_COPY_METHOD, $this->getStore()->getStoreId());
    }

    /**
     * Return guest template id
     *
     * @return mixed
     */
    public function getGuestTemplateId()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $this->getStore()->getStoreId());
    }

    /**
     * Return template id
     *
     * @return mixed
     */
    public function getTemplateId()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_TEMPLATE, $this->getStore()->getStoreId());
    }

    /**
     * Return email identity
     *
     * @return mixed
     */
    public function getEmailIdentity()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_IDENTITY, $this->getStore()->getStoreId());
    }
}
