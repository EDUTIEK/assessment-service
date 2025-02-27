<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

interface Repositories
{
    public function correctorAssignment(): CorrectorAssignmentRepo;
    public function resource(): ResourceRepo;
    public function settings(): SettingsRepo;
    public function writerComment(): WriterCommentRepo;
}
