<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

interface Repositories
{
    public function correctorAssignmentRepo(): CorrectorAssignmentRepo;
    public function resourceRepo(): ResourceRepo;
    public function settingsRepo(): SettingsRepo;
    public function writerCommentRepo(): WriterCommentRepo;
}
