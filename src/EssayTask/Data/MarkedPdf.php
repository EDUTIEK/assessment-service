<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

use DateTimeImmutable;
use Edutiek\AssessmentService\System\Api\HasHtml;

abstract class MarkedPdf implements EssayTaskEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getTaskId(): int;
    abstract public function setTaskId(int $task_id): self;
    abstract public function getWriterId(): int;
    abstract public function setWriterId(int $writer_id): self;
    abstract public function getCorrectorId(): int;
    abstract public function setCorrectorId(int $corrector_id): self;

    /**
     * Get the pdf file with own marks and comments
     */
    abstract public function getOwnPdf(): string;
    abstract public function setOwnPdf(string $own_pdf): self;

    /**
     * Get the pdf file with marks and comments from self and all previous correctors
     */
    abstract public function getSumPdf(): string;
    abstract public function setSumPdf(string $sum_pdf): self;
}
