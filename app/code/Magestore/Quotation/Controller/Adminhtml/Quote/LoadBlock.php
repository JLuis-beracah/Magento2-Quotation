<?php
/**
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Controller\Adminhtml\Quote;

class LoadBlock extends \Magestore\Quotation\Controller\Adminhtml\Quote\QuoteAbstract
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $request = $this->getRequest();
        $isError = false;
        try {
            $this->_initSession()->_processData();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_reloadQuote();
            $this->messageManager->addError($e->getMessage());
            $isError = true;
        } catch (\Exception $e) {
            $this->_reloadQuote();
            $this->messageManager->addException($e, $e->getMessage());
            $isError = true;
        }

        $asJson = $request->getParam('json');
        $block = $request->getParam('block');

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->createPageResult();
        if ($asJson) {
            $resultPage->addHandle('quotation_quote_edit_load_block_json');
        } else {
            $resultPage->addHandle('quotation_quote_edit_load_block_plain');
        }
        if(!$block && $isError){
            $block = 'message';
        }
        if ($block) {
            $blocks = explode(',', $block);
            if ($asJson && !in_array('message', $blocks)) {
                $blocks[] = 'message';
            }

            foreach ($blocks as $block) {
                $resultPage->addHandle('quotation_quote_edit_load_block_' . $block);
            }
        }

        $result = $resultPage->getLayout()->renderElement('content');
        return $this->createRawResult()->setContents($result);
    }
}
