<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

/**
 * Graphical mark drawn by a corrector on the image of a written page
 */
class CorrectionMark
{
    const SHAPE_NONE = '';
    const SHAPE_CIRCLE = 'circle';
    const SHAPE_RECTANGLE = 'rectangle';
    const SHAPE_POLYGON = 'polygon';
    const SHAPE_LINE = 'line';
    const SHAPE_WAVE = 'wave';

    const ALLOWED_SHAPES = [self::SHAPE_CIRCLE, self::SHAPE_RECTANGLE, self::SHAPE_POLYGON, self::SHAPE_LINE, self::SHAPE_WAVE];
    const FILLED_SHAPES = [self::SHAPE_CIRCLE, self::SHAPE_RECTANGLE, self::SHAPE_POLYGON];
    
    private string $key;
    private string $shape;
    private CorrectionMarkPoint $pos;
    private CorrectionMarkPoint $end;
    private int $height;
    private int $width;
    private array $polygon;
    private string $symbol;

    /**
     * Constructor
     * @param string              $key  - unique key of the mark
     * @param string              $shape - type of shape (see constants)
     * @param CorrectionMarkPoint $pos - start position on the image
     * @param CorrectionMarkPoint $end - end position on the image
     * @param int                 $height - height of the mark
     * @param int                 $width - with of the mark
     * @param CorrectionMarkPoint[] $polygon -list of points from a polygon 
     */
    public function __construct(
        string $key,
        string $shape,
        CorrectionMarkPoint $pos,
        CorrectionMarkPoint $end,
        int $height = 0,
        int $width = 0,
        array $polygon = [],
        string $symbol = ''
    ) {
        $this->key = $key;
        $this->shape = $shape;
        $this->pos = $pos;
        $this->end = $end;
        $this->height = $height;
        $this->width = $width;
        $this->polygon = $polygon;
        $this->symbol = $symbol;
    }

    /**
     * Get an object from an assoc array of mark data
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data = [])  : self
    {
        $key = '';
        $shape = self::SHAPE_NONE;
        $pos = new CorrectionMarkPoint(0,0);
        $end = new CorrectionMarkPoint(0,0);
        $width = 0;
        $height = 0;
        $polygon = [];
        $symbol = '';
        
        
        if (isset($data['key'])) {
            $key = (string) $data['key'];
        }
        
        if (isset($data['shape'])) {
            if (in_array($data['shape'], self::ALLOWED_SHAPES)) {
                $shape = (string) $data['shape'];
            }
        }
        if (isset($data['pos'])) {
            $pos = CorrectionMarkPoint::fromArray((array) $data['pos']);
        }
        if (isset($data['end'])) {
            $end = CorrectionMarkPoint::fromArray((array) $data['end']);
        }
        if (isset($data['width'])) {
            $width = (int) $data['width'];
        }
        if (isset($data['height'])) {
            $height = (int) $data['height'];
        }
        if (isset($data['polygon'])) {
            foreach ((array) $data['polygon'] as $point) {
                $polygon[] = CorrectionMarkPoint::fromArray((array) $point);
            }
        }
        if (isset($data['symbol'])) {
            $symbol = (string) $data['symbol'];
        }

        return new self($key, $shape, $pos, $end, $width, $height, $polygon, $symbol);
    }


    /**
     * Get multiple mark objects from a list of mark data
     * @return self[]
     */
    public static function multiFromArray(array $data = []) : array 
    {
        $marks = [];
        foreach ($data as $mark_data)  {
            $marks[] = self::fromArray((array) $mark_data);
        }
        return $marks;
    }

    /**
     * Get a list of mark data from multiple mark objects
     * @param self[] $marks
     */
    public static function multiToArray(array $marks = []) : array
    {
        $data = [];
        foreach ($marks as $mark) {
            $data[] = $mark->toArray();
        }
        return $data;
    }

    /**
     * Get an assoc array of mark data from the objects properties
     * @return array
     */
    public function toArray() : array
    {
        $polygon = [];
        foreach ($this->getPolygon() as $point) {
            $polygon[] = $point->toArray();
        }
        
        return [
            'key' => $this->getKey(),
            'shape' => $this->getShape(),
            'pos' => $this->getPos()->toArray(),
            'end' => $this->getEnd()->toArray(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'polygon' => $polygon,
            'symbol' => $this->getSymbol()
        ];
    }
    

    /**
     * Get the unique key of the mark
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the type of shape (see constants)
     */
    public function getShape(): string
    {
        return $this->shape;
    }

    /**
     * Get the start position on the image
     * This is used by all shapes
     */
    public function getPos(): CorrectionMarkPoint
    {
        return $this->pos;
    }

    /**
     * Get the end position of the shape
     * This is used by line and wave shapes
     * Other shapes have a dummy point (0,0) as end position
     */
    public function getEnd(): CorrectionMarkPoint
    {
        return $this->end;
    }

    /**
     * Get the height of the mark
     * This is only used for rectangles and 0 otherwise
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * Get the width of the mark
     * This is only used for rectangles and 0 otherwise
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * Get a list of points from a polygon
     * @return CorrectionMarkPoint[]
     */
    public function getPolygon(): array
    {
        return $this->polygon;
    }

    /**
     * Get the symbol which is shown in a circle shape
     */
    public function getSymbol(): string
    {
        return $this->symbol;
    }
}