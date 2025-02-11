<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

/**
 * Point specifying an x and y position on an image
 * The position is measured from the top left corner of the image
 * The unit are pixels related to the original images size
 */
class CorrectionMarkPoint
{
    private int $x;
    private int $y;

    /**
     * Constructor
     * @param int $x - x position
     * @param int $y - y position
     */
    public function __construct(int $x = 0, int $y = 0) 
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * Get an object from an assoc array of data
     */
    public static function fromArray($data): self
    {
        $x = 0;
        $y = 0;
        if (is_array($data)) {
            $x = (int) ($data['x'] ?? 0);
            $y = (int) ($data['y'] ?? 0);
        }
        return new self($x, $y);
    }

    /**
     * Get an assoc array of data from the object
     */
    public function toArray() :array 
    {
        return [
            'x' => $this->getX(),
            'y' => $this->getY()
        ];
    }
    
    
    /**
     * Get the X position
     */
    public function getX(): int
    {
        return $this->x;
    }

    /**
     * Get the Y position
     */
    public function getY(): int
    {
        return $this->y;
    }

}