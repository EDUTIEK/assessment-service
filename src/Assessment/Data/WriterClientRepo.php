<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface WriterClientRepo
{
    public function new(): WriterClient;

    public function oneByWriterIdAndSessionId(int $writer_id, string $session_id): ?WriterClient;

    public function oneByWriterIdAndTokenId(int $writer_id, int $token_id): ?WriterClient;

    /** @return WriterClient[] */
    public function allByWriterId(int $writer_id): array;

    public function save(WriterClient $client): void;

    public function deleteByWriterIdAndTokenId(int $writer_id, int $token_id): void;

    public function deleteByWriterId(int $writer_id): void;
}
