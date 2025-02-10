<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfCreator;

use Edutiek\AssessmentService\System\Data\ImageDescriptor;

class Service implements FullService
{
    /**
     * Page orientation (P=portrait, L=landscape).
     */
    protected $page_orientation = 'P';

    /**
     * Document unit of measure [pt=point, mm=millimeter, cm=centimeter, in=inch].
     */
    protected  $pdf_unit = 'mm';

    /**
     * Page format.
     */
    protected $page_format = 'A4';


    /**
     * Main text of the page
     */
    protected $main_font = 'times';
    protected $main_font_size = 10;

    protected $header_font = 'helvetica';
    protected $header_font_size = 12;

    protected $footer_font = 'helvetica';
    protected $footer_font_size = 10;

    protected $mono_font = 'courier';


    public function __construct(
       private string $absolute_temp_path,
       private string $relative_temp_path
    ) {}

    public function createPdf(
        array $parts,
        string $creator = "",
        string $author = "",
        string $title = "",
        string $subject = "",
        string $keywords = ""
    ) : string
    {
        // create new PDF document
        // note the last parameter for compliance with PDF/A-2B
        $pdf = new Tcpdf($this->page_orientation, $this->pdf_unit, $this->page_format, true, 'UTF-8', false, 2);

        $pdf->setAllowLocalFiles(true);

        // set document information
        $pdf->SetCreator($creator);
        $pdf->SetAuthor($author);
        $pdf->SetTitle($title);
        $pdf->SetSubject($subject);
        $pdf->SetKeywords($keywords);

        $pdf->SetAlpha(1);

        // set default header data
        $pdf->SetHeaderData('', 0, $title, $subject);

        // set header and footer fonts
        $pdf->setHeaderFont(Array($this->header_font, '', $this->header_font_size));
        $pdf->setFooterFont(Array($this->footer_font, '', $this->footer_font_size));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont($this->mono_font);

        // Set font
        $pdf->SetFont($this->main_font, '', $this->main_font_size, '', true);


        $pdf->setDisplayMode('fullpage', 'SinglePage', 'UseThumbs');

        foreach ($parts as $part)
        {
            $pdf->SetMargins($part->getLeftMargin(), $part->getTopMargin(), $part->getRightMargin(), true);
            $pdf->setPrintHeader($part->getPrintHeader());
            $pdf->setHeaderMargin($part->getHeaderMargin());
            $pdf->setPrintFooter($part->getPrintFooter());
            $pdf->setFooterMargin($part->getFooterMargin());

            $pdf->AddPage($part->getOrientation(), $part->getFormat(), true);

            foreach ($part->getElements() as $element)
            {
                if ($element instanceof PdfHtml) {
                    $pdf->SetAutoPageBreak(true, $part->getBottomMargin());
                    $pdf->writeHtmlCell(
                        (float) $element->getWidth(),
                        (float) $element->getHeight(),
                        $element->getLeft(),
                        $element->getTop(),
                        $element->getHtml(),
                        0,      // border
                        0,      // ln
                        false,  // fill
                        true,   // reseth
                        '',     // align
                        true    // autopadding
                    );
                }
                elseif ($element instanceof PdfImage) {

                    $pdf->SetAutoPageBreak(false);
                    $pdf->Image(
                        $element->getPath(),
                        (float) $element->getLeft(),
                        (float) $element->getTop(),
                        (float) $element->getWidth(),
                        (float) $element->getHeight(),
                        '',
                        '',
                        '',
                        true,
                        300,
                        '',
                        false,
                        false,
                        0,
                        false,
                        false,
                        false,
                        false,
                        array()
                    );
                }
            }

            // important to do this here to avoid an overlapping with next part if html content is longer than a page
            $pdf->lastPage();
        }

        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        return $pdf->Output('dummy.pdf', 'S');
    }

    public function createStandardPart(array $elements = [], ?PdfSettings $pdf_settings = null): PdfPart
    {
        $part = new PdfPart(
            PdfPart::FORMAT_A4,
            PdfPart::ORIENTATION_PORTRAIT,
            $elements
        );

        if ($pdf_settings !== null) {
            return $part
                ->withTopMargin($pdf_settings->getContentTopMargin())
                ->withBottomMargin($pdf_settings->getContentBottomMargin())
                ->withLeftMargin($pdf_settings->getLeftMargin())
                ->withRightMargin($pdf_settings->getRightMargin())
                ->withHeaderMargin($pdf_settings->getHeaderMargin())
                ->withFooterMargin($pdf_settings->getFooterMargin())
                ->withPrintHeader($pdf_settings->getAddHeader())
                ->withPrintFooter($pdf_settings->getAddFooter());
        }

        return $part;
    }

    public function getImagePathForPdf(?ImageDescriptor $image): string {
        if ($image !== null) {
            $content = stream_get_contents($image->stream());
            $file = tempnam($this->absolute_temp_path, 'LAS');
            file_put_contents ($file, $content);
            return $this->relative_temp_path . '/' . basename($file);
        }
        return '';
    }
}
