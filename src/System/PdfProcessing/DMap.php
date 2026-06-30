<?php declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfProcessing;

class DMap
{
    private array $keys = [];
    private array $values = [];

    public function has(string $key): bool
    {
        return isset($this->values[$key]);
    }

    public function get(string $key): ?string
    {
        return $this->values[$key] ?? null;
    }

    public function hasValue(string $value): bool
    {
        return isset($this->keys[$value]);
    }

    public function set(string $key, string $value): void
    {
        $this->keys[$value] = $key;
        $this->values[$key] = $value;
    }
}
