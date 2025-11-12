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

    public function createPdf(string $html, Options $options): string
    {
        $pdf = $this->initPdf($options);

        $header = $options->getPrintHeader() ? ('<header> ' . $options->getSubject() . '</header>') : '';
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

        return $pdf->output();
    }

    public function createPdfFromParts(array $elements, Options $options): string
    {
        $html_parts = array_map(fn(PdfElement $el) => match (get_class($el)) {
            PdfImage::class => sprintf('<img src="data:image/png;base64,%s" %s/>', base64_encode(file_get_contents($el->getPath())), $this->style($el)),
            PdfHtml::class => sprintf('<div %s>%s</div>', $this->style($el), $el->getHtml()),
        }, $elements);

        return $this->createPdf(join($this->pageBreak(), $html_parts), $options);
    }

    public function options(?PdfSettings $pdf_settings): Options
    {
        if (!$pdf_settings) {
            return new Options();
        }

        return (new Options())
            ->withTopMargin($pdf_settings->getContentTopMargin())
            ->withBottomMargin($pdf_settings->getContentBottomMargin())
            ->withLeftMargin($pdf_settings->getLeftMargin())
            ->withRightMargin($pdf_settings->getRightMargin())
            ->withHeaderMargin($pdf_settings->getHeaderMargin())
            ->withFooterMargin($pdf_settings->getFooterMargin())
            ->withPrintHeader($pdf_settings->getAddHeader())
            ->withPrintFooter($pdf_settings->getAddFooter());
    }

    private function pageBreak(): string
    {
        return '<div class="force-new-page"></div>';
    }

    private function style(PdfElement $part): string
    {
        return sprintf(
            'style="margin-top: %dmm ; margin-left: %dmm; width: %dmm; height: %dmm"',
            $part->getTop(),
            $part->getLeft(),
            $part->getWidth(),
            $part->getHeight(),
        );
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

    private function mm2px(float $mm): float
    {
        return $mm * 3.78;
    }

    private function css(Options $options): string
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
    top: ' . $options->getTopMargin() . 'mm;
    left: ' . $options->getLeftMargin() . 'mm;
    right: ' . $options->getRightMargin() . 'mm;
    transform: translateY(-100%);
    height: 20px;
    border-bottom: 1px solid black;
    right: 0;
}';
    }

    private function renderPageNumbers(Options $options): Closure
    {
        $right = $options->getRightMargin();
        $bot = $options->getFooterMargin();
        return function(int $page, int $max_pages, Canvas $canvas, FontMetrics $font_metrics) use ($options, $right, $bot): void {
            $text = (string) ($page + $options->getStartPageNumber() -1);
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
        $pdf->addInfo('Creator', $opts->getCreator());
        $pdf->addInfo('Author', $opts->getAuthor());
        $pdf->addInfo('Title', $opts->getTitle());
        $pdf->addInfo('Subject', $opts->getSubject());
        $pdf->addInfo('Keywords', $opts->getKeywords());

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
