<?php
/**
 *
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Quotation\Controller\Adminhtml\Quote;

use Magestore\Quotation\Model\Source\Quote\Status as QuoteStatus;

class AddComment extends \Magestore\Quotation\Controller\Adminhtml\Quote\QuoteAbstract
{
    /**
     * Add quote comment action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->_initSession();
        $quote = $this->_getQuote();
        if ($quote) {
            try {
                $data = $this->getRequest()->getPost('history');
                if (empty($data['comment']) && $data['status'] == $quote->getDataByKey('status')) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a comment.'));
                }

                $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                $visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;
                $comment = $data['comment'];
                $status = $data['status'];
                $isAdminQuote = ($quote->getRequestStatus() ==  QuoteStatus::STATUS_ADMIN_PENDING)?true:false;
                $this->quotationManagement->addAdminComment($quote, $comment, $status, $visible, $notify);
                if($isAdminQuote && ($status != QuoteStatus::STATUS_ADMIN_PENDING)){
                    $this->_getSession()->reset();
                    $this->resetNewQuotationSession();
                    return $this->resultRedirectFactory->create()->setPath('quotation/quote/edit', ["quote_id" => $quote->getId()]);
                }
                return $this->createPageResult();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response = ['error' => true, 'message' => $e->getMessage()];
            } catch (\Exception $e) {
                $response = ['error' => true, 'message' => __('We cannot add quote comment history.')];
            }
            if (is_array($response)) {
                $resultJson = $this->createJsonResult();
                $resultJson->setData($response);
                return $resultJson;
            }
        }
        return $this->resultRedirectFactory->create()->setPath('quotation/*/');
    }
}
