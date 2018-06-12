<?php
/**
 * Mail Template Transport Builder
 *
 * Copyright © Magestore, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Quotation\Model\Quote\Email;

/**
 * Class TransportBuilder
 * @package Magestore\Quotation\Model\Quote\Email
 */
class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    /**
     * @var bool
     */
    protected $attachPdf = false;

    /**
     * @var string
     */
    protected $pdf_file_name = "";

    /**
     * @param bool $attachPdf
     */
    public function setAttachPdf(bool $attachPdf){
        $this->attachPdf = $attachPdf;
    }

    /**
     * @return bool
     */
    public function getAttachPdf(){
        return $this->attachPdf;
    }

    /**
     * @param string $pdfFileName
     */
    public function setPdfFilename($pdfFileName = ""){
        $this->pdf_file_name = $pdfFileName;
    }

    /**
     * @return string
     */
    public function getPdfFilename(){
        return $this->pdf_file_name;
    }

    /**
     * Prepare message
     *
     * @return $this
     */
    protected function prepareMessage()
    {
        parent::prepareMessage();
        $isAttachPdf = $this->getAttachPdf();
        $pdfFileName = $this->getPdfFilename();
        if($isAttachPdf && $pdfFileName){
            $this->generatePdfAttachment($pdfFileName);
        }
        return $this;
    }

    /**
     * @param $fileName
     * @return $this
     * @throws \Zend_Pdf_Exception
     */
    protected function generatePdfAttachment($fileName)
    {
        $pdfObject = $this->message->getBody();
        $pdf = new \Zend_Pdf();
        $page = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
        $pdf->pages[] = $page;
        $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA), 20);  //Set Font
        $page->rawWrite($pdfObject->getContent());
        $pdfData = $pdf->render();
        $this->message->createAttachment(
            $pdfData,
            'application/pdf',
            \Zend_Mime::DISPOSITION_ATTACHMENT,
            \Zend_Mime::ENCODING_BASE64,
            $fileName
        );
        return $this;
    }
}
