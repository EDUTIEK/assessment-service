<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

interface Repositories
{
    public function correctionSettingsRepo(): CorrectionSettingsRepo;
    public function correctorCommentRepo(): CorrectorCommentRepo;
    public function correctorPointsRepo(): CorrectorPointsRepo;
    public function correctorPrefsRepo(): CorrectorPrefsRepo;
    public function correctorSummaryRepo(): CorrectorSummaryRepo;
    public function correctorTaskPrefsRepo(): CorrectorTaskPrefsRepo;
    public function essayRepo(): EssayRepo;
    public function essayImageRepo(): EssayImageRepo;
    public function ratingCriterionRepo(): RatingCriterionRepo;
    public function taskSettingsRepo(): TaskSettingsRepo;
    public function writerHistoryRepo(): WriterHistoryRepo;
    public function writerNoticeRepo(): WriterNoticeRepo;
    public function writerPrefsRepo(): WriterPrefsRepo;
    public function writingSetingsRepo(): WritingSettingsRepo;
}
