<?php

namespace Edutiek\AssessmentService\System\PdfCreator;

class Tcpdf extends \TCPDF
{
    public function Footer() {

        $margins = $this->getMargins();
        // hack, but works with different right margins
        $rightMargin = (int) $margins['right'] + 12;
        $footerMargin = (int) $margins['footer'];

        $this->SetY(-$footerMargin);
        $this->SetX(-$rightMargin);

        // Set font
        $this->SetFont('helvetica', 'I', 8);

        // Page number
        $this->Cell(0, 10, $this->getAliasNumPage().' / '.$this->getAliasNbPages(), 0, false, 'L', 0, '', 0, false, 'B', 'B');
    }
}