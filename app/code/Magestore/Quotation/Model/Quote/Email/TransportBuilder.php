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
        if (class_exists('\Dompdf\Dompdf')) {
            $template = $this->getTemplate();
            $html = $template->processTemplate();
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->set_option('isHtml5ParserEnabled', true);
            $dompdf->set_option('isRemoteEnabled', true);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $this->message->createAttachment(
                $dompdf->output(),
                'application/pdf',
                \Zend_Mime::DISPOSITION_ATTACHMENT,
                \Zend_Mime::ENCODING_BASE64,
                $fileName
            );
        }
        return $this;
    }
}
