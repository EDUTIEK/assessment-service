<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

class ImageDescriptor
{
    /**
     * @param resource $stream
     */
    public function __construct(
        private $stream,
        private readonly int $width,
        private readonly int $height,
        private readonly string $type)
    {
    }

    /**
     * @return resource
     */
    public function stream()
    {
        return $this->stream;
    }

    public function width(): int
    {
        return $this->width;
    }

    public function height(): int
    {
        return $this->height;
    }

    public function type(): string
    {
        return $this->type;
    }
}