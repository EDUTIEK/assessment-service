<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

interface WriterPrefsRepo
{
    public function new(): WriterPrefs;
    public function one(int $writer_id): ?WriterPrefs;
    public function save(WriterPrefs $entity): void;
    public function delete(int $writer_id): void;
}