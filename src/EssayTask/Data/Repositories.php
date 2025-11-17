<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

interface Repositories
{
    public function essay(): EssayRepo;
    public function essayImage(): EssayImageRepo;
    public function writingStep(): WritingStepRepo;
    public function writerNotice(): WriterNoticeRepo;
    public function writerPrefs(): WriterPrefsRepo;
    public function writingSettings(): WritingSettingsRepo;
}
