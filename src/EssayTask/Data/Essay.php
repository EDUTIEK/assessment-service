<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

use DateTimeImmutable;
use Edutiek\AssessmentService\System\Api\HasHtml;

abstract class Essay implements EssayTaskEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getTaskId(): int;
    abstract public function setTaskId(int $task_id): self;
    abstract public function getUuid(): string;
    abstract public function setUuid(string $uuid): self;
    abstract public function getWriterId(): int;
    abstract public function setWriterId(int $writer_id): self;
    #[HasHtml]
    abstract public function getWrittenText(): ?string;
    abstract public function setWrittenText(?string $written_text): self;
    abstract public function getRawTextHash(): string;
    abstract public function setRawTextHash(string $raw_text_hash): self;
    abstract public function getPdfVersion(): ?string;
    abstract public function setPdfVersion(?string $pdf_version): self;
    abstract public function getLastChange(): ?DateTimeImmutable;
    abstract public function setLastChange(?DateTimeImmutable $last_change): self;
    abstract public function getServiceVersion(): int;
    abstract public function setServiceVersion(int $service_version): self;
    abstract public function getFirstChange(): ?DateTimeImmutable;
    abstract public function setFirstChange(?DateTimeImmutable $first_change): self;
    abstract public function hasPdfFromWrittenText(): bool;
    abstract public function setPdfFromWrittenText(bool $pdf_from_written_text): self;

    public function getWordCount(): int
    {
        return str_word_count($this->getWrittenText() ?? "");
    }

    public function hasPDFVersion(): bool
    {
        return $this->getPdfVersion() !== null;
    }

    public function touch(): self
    {
        $now = new DateTimeImmutable();
        if (!$this->getFirstChange()) {
            $this->setFirstChange($now);
        }
        $this->setLastChange($now);
        return $this;
    }
}
