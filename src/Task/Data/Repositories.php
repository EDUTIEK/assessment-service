<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

interface Repositories
{
    public function correctorTaskPrefs(): CorrectorTaskPrefsRepo;
    public function correctionSettings(): CorrectionSettingsRepo;
    public function correctorAssignment(): CorrectorAssignmentRepo;
    public function correctorSummary(): CorrectorSummaryRepo;
    public function correctorComment(): CorrectorCommentRepo;
    public function correctorPoints(): CorrectorPointsRepo;
    public function correctorPrefs(): CorrectorPrefsRepo;
    public function resource(): ResourceRepo;
    public function settings(): SettingsRepo;
    public function writerComment(): WriterCommentRepo;
}
