<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

interface Repositories
{
    public function correctorAssignment(): CorrectorAssignmentRepo;
    public function correctorComment(): CorrectorCommentRepo;
    public function correctorPoints(): CorrectorPointsRepo;
    public function resource(): ResourceRepo;
    public function settings(): SettingsRepo;
    public function writerComment(): WriterCommentRepo;
}
