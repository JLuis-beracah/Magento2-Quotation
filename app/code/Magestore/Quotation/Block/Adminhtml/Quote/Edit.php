<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Block\Adminhtml\Quote;

/**
 * Class Edit
 * @package Magestore\Quotation\Block\Adminhtml\Quote
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magestore\Quotation\Model\BackendSession
     */
    protected $quoteSession;

    /**
     * @var \Magestore\Quotation\Api\QuotationManagementInterface
     */
    protected $quotationManagement;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Edit constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magestore\Quotation\Model\BackendSession $quoteSession
     * @param \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magestore\Quotation\Model\BackendSession $quoteSession,
        \Magestore\Quotation\Api\QuotationManagementInterface $quotationManagement,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->quoteSession = $quoteSession;
        $this->quotationManagement = $quotationManagement;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'order_id';
        $this->_controller = 'quotation_quote';
        $this->_mode = 'edit';

        parent::_construct();

        $this->setId('quotation_quote_edit');
        $this->buttonList->update('back', 'onclick', 'setLocation(\'' . $this->getBackUrl() . '\')');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('save');

        $quote = $this->_getQuote();
        if($quote){
            $this->quotationManagement->isExpired($quote);
            $canEdit = $this->quotationManagement->canEdit($quote);
            if($canEdit){
                $this->buttonList->add('save_as_draft', [
                    'label' => __("Save as Draft"),
                    'class' => 'save_as_draft',
                    'onclick' => "quote.submit()"
                ]);
            }

            $canDecline = $this->quotationManagement->canDecline($quote);
            if($canDecline){
                $confirm = __('Are you sure to decline this quote request?');
                $this->buttonList->add('decline', [
                    'label' => __("Decline"),
                    'class' => 'decline',
                    'onclick' => "quote.decline('{$confirm}')"
                ]);
            }

            $canSend = $this->quotationManagement->canSend($quote);
            if($canSend){
                $confirm = __('Are you sure to send this quote to customer?');
                $this->buttonList->add('send', [
                    'label' => __("Send"),
                    'class' => 'send primary',
                    'onclick' => "quote.send('{$confirm}')"
                ]);
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $pageTitle = $this->getLayout()->createBlock(
            \Magestore\Quotation\Block\Adminhtml\Quote\Edit\Header::class
        )->toHtml();
        if (is_object($this->getLayout()->getBlock('page.title'))) {
            $this->getLayout()->getBlock('page.title')->setPageTitle($pageTitle);
        }
        return parent::_prepareLayout();
    }

    /**
     * Prepare header html
     *
     * @return string
     */
    public function getHeaderHtml()
    {
        $out = '<div id="quote-header">' . $this->getLayout()->createBlock(
                \Magestore\Quotation\Block\Adminhtml\Quote\Edit\Header::class
            )->toHtml() . '</div>';
        return $out;
    }

    /**
     * Get header width
     *
     * @return string
     */
    public function getHeaderWidth()
    {
        return 'width: 70%;';
    }

    /**
     * @return \Magestore\Quotation\Model\BackendSession
     */
    protected function _getSession()
    {
        return $this->quoteSession;
    }

    /**
     * @return \Magento\Quote\Model\Quote|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getQuote()
    {
        if ($this->hasQuote()) {
            return $this->getData('quote');
        }
        if ($this->registry->registry('current_quote_request')) {
            return $this->registry->registry('current_quote_request');
        }
        if ($this->registry->registry('quote')) {
            return $this->registry->registry('quote');
        }
        throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t get the quote instance right now.'));
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('quotation/quote/');
    }
}
