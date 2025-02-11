<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ImageSketch\ImageMagick;

use Imagick;
use ImagickDraw;
use Edutiek\AssessmentService\System\ImageSketch\FullService;
use Edutiek\AssessmentService\System\ImageSketch\Point;
use Exception;
use Closure;

class Sketch implements FullService
{
    private string $output_format;
    private int $output_quality;
    private int $output_dpi;
    private int $font_size;
    private Closure $set_font;

    /**
     * @param array{
     *     ?output_format: string,
     *     ?font: array{?name: string, ?path: string, ?size: int}
     * } $config
     */
    public function __construct(array $config = [])
    {
        $config = array_replace_recursive([
            'output_format' => 'JPG',
            'output_quality' => 20,
            'output_dpi' => 300,
            'font' => ['name' => 'FreeSerif', 'size' => 10],
        ], $config);

        $this->assertSupportedFormat($config['output_format']);

        $this->output_format = $config['output_format'];
        $this->output_quality = $config['output_quality'];
        $this->output_dpi = $config['output_dpi'];
        $this->font_size = $config['font']['size'];
        $this->set_font = isset($config['font']['path']) ?
                        fn (ImagickDraw $draw) => $draw->setFontFamily($config['font']['path']) : (
                        isset($config['font']['name']) ?
                            fn (ImagickDraw $draw) => $draw->setFont($config['font']['name']):
                            fn (ImagickDraw $draw)  => $draw->getFont()
                        );
                            
    }

    public function applyShapes(array $shapes, $image)
    {
        $magic = new Imagick();
        $magic->readImageFile($image);
        $magic->setImageCompressionQuality($this->output_quality);
        $magic->setOption('density', (string) $this->output_dpi);

        foreach ($shapes as $shape) {
            $draw = new ImagickDraw();
            ($this->set_font)($draw);
            $draw->setFontSize($this->font_size);
            $draw->setFillColor('#00000000');
            $shape->draw(new Draw($draw, $magic));
            $magic->drawImage($draw);
        }

        $stream = $this->asStream($magic);
        $magic->clear();
        unset($magick);

        return $stream;
    }

    private function asStream(Imagick $magic)
    {
        $fd = fopen('php://temp', 'w+');
        $magic->writeImageFile($fd, $this->output_format);
        rewind($fd);

        return $fd;
    }

    private function assertSupportedFormat(string $format): void
    {
        if (!in_array($format, (new Imagick())->queryFormats(), true)) {
            throw new Exception('Image format "' . $format . '" is not supported by image magick.');
        }
    }
}
