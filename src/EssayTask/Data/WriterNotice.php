<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

use DateTimeImmutable;

abstract class WriterNotice implements EssayTaskEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): self;
    public abstract function getEssayId(): int;
    public abstract function setEssayId(int $essay_id): self;
    public abstract function getNoteNo(): int;
    public abstract function setNoteNo(int $note_no): self;
    public abstract function getNoteText(): ?string;
    public abstract function setNoteText(?string $note_text): self;
    public abstract function getLastChange(): ?DateTimeImmutable;
    public abstract function setLastChange(?DateTimeImmutable $last_change): self;
}
