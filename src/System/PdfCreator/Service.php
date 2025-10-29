<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfCreator;

use Edutiek\AssessmentService\System\Data\ImageDescriptor;
use Closure;
use Dompdf\Dompdf;
use Dompdf\Canvas;
use Dompdf\FontMetrics;

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

    /**
     * @param Closure(): Dompdf $dom_pdf
     */
    public function __construct(
       private string $absolute_temp_path,
       private string $relative_temp_path,
       private Closure $dom_pdf
    ) {}

    public function createPdf(
        array $parts,
        string $creator = "",
        string $author = "",
        string $title = "",
        string $subject = "",
        string $keywords = ""
    ): string
    {
        return $this->createPdfWithDompdf($parts, $creator, $author, $title, $subject, $keywords);
    }

    public function createPdfWithDompdf(
        array $parts,
        string $creator,
        string $author,
        string $title,
        string $subject,
        string $keywords
    ): string
    {
        $pdf = $this->initPdf($creator, $author, $title, $subject, $keywords);

        [$pages, $css] = $this->renderParts($parts, $subject);
        $css .= $this->css();
        $html = sprintf(
            '<!DOCTYPE html><html><head><meta charset="utf-8"/><style>%s</style></head><body>%s</body></html>',
            $css,
            join('<div class="force-new-page"></div>', $pages),
        );
        $pdf->loadHtml($html);
        $pdf->render();

        // Cannot change this per part because we don't know when a new part begins in the page_script callback,
        // as one part can be rendered as multiple pages.
        $part = current($parts);
        if ($part && $part->getPrintFooter()) {
            $pdf->getCanvas()->page_script($this->renderPageNumbers(
                $this->mm2px($part->getRightMargin()),
                $this->mm2px($part->getFooterMargin())
            ));
        }

        return $pdf->output();
    }

    public function createPdfWithTCPDF(
        array $parts,
        string $creator = "",
        string $author = "",
        string $title = "",
        string $subject = "",
        string $keywords = ''
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

    /**
     * @param PdfPart[] $parts
     * @return array{0: string[], 1: string}
     */
    private function renderParts(array $parts, string $subject): array
    {
        $pages = [];
        $css = '';
        $id = 0;
        foreach ($parts as $part)
        {
            $id++;
            $html = $this->renderElements($part->getElements());
            if ($html) {
                if ($part->getPrintHeader()) {
                    $html = $this->header($subject, $part) . $html;
                }
                $html_id = 'part_' . $id;
                // Add div for each page, so custom page margins can be set.
                $pages[] = sprintf('<div id="%s">%s</div>', $html_id, $html);
                $css .= sprintf(
                    '#%s{margin: %dmm %dmm %dmm %dmm;}',
                    $html_id,
                    $part->getTopMargin(),
                    $part->getRightMargin(),
                    $part->getBottomMargin(),
                    $part->getLeftMargin()
                );
            }
        }

        return [$pages, $css];
    }

    /**
     * @param PdfElement[] $elements
     */
    private function renderElements(array $elements): string
    {
        $html = '';
        foreach ($elements as $element) {
            if ($element instanceof PdfHtml) {
                $html .= $element->getHtml();
            }
            elseif ($element instanceof PdfImage) {
                // Dompdf doesn't allow loading files from any place. To allow from which folder files can be loaded set:
                // $pdf->getOptions()->set('chroot', '/your/directory');
                // But as it isn't known if all images are located in the same folder, base64 is used instead.
                $html .= sprintf('<p><img src="data:image/png;base64,%s"/></p>', base64_encode(file_get_contents($element->getPath())));
            }
        }

        return $html;
    }

    private function header(string $content, PdfPart $part): string
    {
        return sprintf(
            '<header style="top: %dmm; left: %dmm; right: %dmm; transform: translateY(-100%%);">%s</header>',
            $part->getTopMargin(),
            $part->getLeftMargin(),
            $part->getRightMargin(),
            $content
        );
    }

    private function mm2px(float $mm): float
    {
        return $mm * 3.78;
    }

    private function css(): string
    {
        return '
.force-new-page
{
    page-break-after: always;
}
body
{
    font-size: ' . $this->main_font_size . ';
}
:root
{
    margin: 0;
}
header
{
    font-family: ' . $this->header_font . ';
    font-size: ' . $this->header_font_size . ';
    position: fixed;
    top: 0;
    left: 0;
    height: 20px;
    border-bottom: 1px solid black;
    right: 0;
}';
    }

    /**
     * @param float $right_margin in px
     * @param float $bottom_margin in px
     */
    private function renderPageNumbers(float $right_margin, float $bottom_margin): Closure
    {
        return function(int $page, int $max_pages, Canvas $canvas, FontMetrics $font_metrics) use ($right_margin, $bottom_margin): void {
            $text = $page .  '/' . $max_pages;
            $font = $font_metrics->getFont($this->footer_font);
            $w = $font_metrics->getTextWidth($text, $font, $this->footer_font_size);
            $h = $font_metrics->getFontHeight($font, $this->footer_font_size);
            $canvas->text(
                $canvas->get_width() - $w - $right_margin,
                $canvas->get_height() - $h - $bottom_margin,
                $text,
                $font,
                $this->footer_font_size
            );
        };
    }

    private function initPdf(
        string $creator,
        string $author,
        string $title,
        string $subject,
        string $keywords,
    ): Dompdf
    {
        $pdf = ($this->dom_pdf)();
        $pdf->setPaper($this->page_format);
        $pdf->addInfo('Creator', $creator);
        $pdf->addInfo('Author', $author);
        $pdf->addInfo('Title', $title);
        $pdf->addInfo('Subject', $subject);
        $pdf->addInfo('Keywords', $keywords);

        $options = $pdf->getOptions();
        $options->set('isPdfAEnabled', true);
        $options->set('defaultFont', $this->main_font);
        // $options->setDpi(150);
        // $options->setChroot($dir);
        // $options->set('fontDir', $tmp);
        // $options->set('fontCache', $tmp);
        // $options->set('tempDir', $tmp);
        $pdf->setOptions($options);
        $this->setupFonts($pdf);

        return $pdf;
    }

    private function setupFonts(Dompdf $pdf): void
    {
        $font_metrics = $pdf->getFontMetrics();
        $font_metrics->setFontFamily('courier', $font_metrics->getFamily('DejaVu Sans Mono'));
        $font_metrics->setFontFamily('fixed', $font_metrics->getFamily('DejaVu Sans Mono'));
        $font_metrics->setFontFamily('helvetica', $font_metrics->getFamily('DejaVu Sans'));
        $font_metrics->setFontFamily('monospace', $font_metrics->getFamily('DejaVu Sans Mono'));
        $font_metrics->setFontFamily('sans-serif', $font_metrics->getFamily('DejaVu Sans'));
        $font_metrics->setFontFamily('serif', $font_metrics->getFamily('DejaVu Serif'));
        $font_metrics->setFontFamily('times', $font_metrics->getFamily('DejaVu Serif'));
        $font_metrics->setFontFamily('times-roman', $font_metrics->getFamily('DejaVu Serif'));
    }
}
