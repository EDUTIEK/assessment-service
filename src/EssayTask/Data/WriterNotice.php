<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

use DateTimeImmutable;
use Edutiek\AssessmentService\System\Api\HasHtml;

abstract class WriterNotice implements EssayTaskEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getEssayId(): int;
    abstract public function setEssayId(int $essay_id): self;
    abstract public function getNoteNo(): int;
    abstract public function setNoteNo(int $note_no): self;
    #[HasHtml]
    abstract public function getNoteText(): ?string;
    abstract public function setNoteText(?string $note_text): self;
    abstract public function getLastChange(): ?DateTimeImmutable;
    abstract public function setLastChange(?DateTimeImmutable $last_change): self;
}
