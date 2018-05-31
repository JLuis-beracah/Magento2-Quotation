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
     * Session quote
     *
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_sessionQuote;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        array $data = []
    ) {
        $this->_sessionQuote = $sessionQuote;
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


        $this->buttonList->add('save_as_draft', [
            'label' => __("Save as Draft"),
            'class' => 'save_as_draft',
            'onclick' => "quote.submit()"
        ]);

        $confirm = __('Are you sure you decline this quote request?');
        $this->buttonList->add('decline', [
            'label' => __("Decline"),
            'class' => 'decline',
            'onclick' => "quote.decline('{$confirm}')"
        ]);

        $confirm = __('Are you sure you send this quote to customer?');
        $this->buttonList->add('send', [
            'label' => __("Send"),
            'class' => 'send primary',
            'onclick' => "quote.send('{$confirm}')"
        ]);
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
     * Retrieve quote session object
     *
     * @return \Magento\Backend\Model\Session\Quote
     */
    protected function _getSession()
    {
        return $this->_sessionQuote;
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
