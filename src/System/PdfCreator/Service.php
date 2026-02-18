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
    private const FONT_DIR = __DIR__ . '/../fonts';
    /**
     * Main text of the page
     */
    protected $main_font = 'times';
    protected $main_font_size = 10;

    protected $header_font = 'helvetica';
    protected $header_font_size = 8;

    protected $footer_font = 'helvetica';
    protected $footer_font_size = 8;

    protected $mono_font = 'courier';

    /**
     * @param Closure(): Dompdf $dom_pdf
     */
    public function __construct(
        private Closure $dom_pdf
    ) {
    }

    public function createPdf(string $html, Options $options): string
    {
        $pdf = $this->initPdf($options);

        $header = $options->getPrintHeader() ? ('<header> ' . $options->getTitle() . '</header>') : '';
        $pdf->loadHtml(sprintf(
            '<!DOCTYPE html><html><head><meta charset="utf-8"/><style>%s</style></head><body>%s%s</body></html>',
            $this->css($options),
            $header,
            $html,
        ));

        $pdf->render();

        if ($options->getPrintFooter()) {
            $pdf->getCanvas()->page_script($this->renderPageNumbers($options));
        }

        $pdf->addInfo('Creator', 'Fred' . $options->getCreator());
        $pdf->addInfo('Author', $options->getAuthor());
        $pdf->addInfo('Title', $options->getTitle());
        $pdf->addInfo('Subject', $options->getSubject());
        $pdf->addInfo('Keywords', $options->getKeywords());

        return $pdf->output();
    }

    private function pageBreak(): string
    {
        return '<div class="force-new-page"></div>';
    }

    private function mm2px(float $mm): float
    {
        return $mm * 3.78;
    }

    private function css(Options $options): string
    {
        $font_dir = self::FONT_DIR;
        return '
        
@page { 
margin: 0px; 
}
        
@font-face
{
    font-family: sc;
    font-weight: normal;
    font-style: normal;
    src: url(' . $font_dir . '/NotoSansCJKsc-VF.ttf) format(truetype);
}
@font-face
{
    font-family: tc;
    font-weight: normal;
    font-style: normal;
    src: url(' . $font_dir . '/NotoSansCJKtc-VF.ttf) format(truetype);
}
@font-face
{
    font-family: Math;
    font-weight: normal;
    font-style: normal;
    src: url(' . $font_dir . '/NotoSansMath-Regular.ttf) format(truetype);
}
.force-new-page
{
    page-break-after: always;
}

html {
    margin: 0;
    margin-left: ' . $options->getLeftMargin() . 'mm;
    margin-right: ' . $options->getRightMargin() . 'mm;
    font-family: ' . $this->main_font . ', sc, tc, Math;
    font-size: ' . $this->main_font_size . ';

}

body
{
    margin: 0;
    margin-top: ' . $options->getTopMargin() . 'mm;
    margin-bottom: ' . $options->getBottomMargin() . 'mm;
}

pre {
    max-width: 200mm;
    overflow-x: hidden;
}

:root
{

}
header
{
    font-family: ' . $this->header_font . ';
    font-size: ' . $this->header_font_size . ';
    position: fixed;
    top: ' . $options->getHeaderMargin() . 'mm;
    transform: translateY(-100%);
}';
    }

    private function renderPageNumbers(Options $options): Closure
    {
        $right = $options->getRightMargin();
        $bot = $options->getFooterMargin();
        return function (int $page, int $max_pages, Canvas $canvas, FontMetrics $font_metrics) use ($options, $right, $bot): void {
            $text = (string) ($page + $options->getStartPageNumber() - 1);
            $font = $font_metrics->getFont($this->footer_font);
            $w = $font_metrics->getTextWidth($text, $font, $this->footer_font_size);
            $h = $font_metrics->getFontHeight($font, $this->footer_font_size);
            $canvas->text(
                $canvas->get_width() - $w - $right,
                $canvas->get_height() - $h - $bot,
                $text,
                $font,
                $this->footer_font_size
            );
        };
    }

    private function initPdf(Options $opts): Dompdf
    {
        $pdf = ($this->dom_pdf)();
        $pdf->setPaper('A4', $opts->getPortrait() ? 'portrait' : 'landscape');

        $options = $pdf->getOptions();
        $options->set('isPdfAEnabled', true);
        $options->set('defaultFont', $this->main_font);
        // $options->setDpi(150);
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
