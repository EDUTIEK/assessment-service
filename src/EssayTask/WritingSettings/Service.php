<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\EssayTask\WritingSettings;

use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\EssayTask\WritingSettings\FullService;
use ILIAS\Plugin\LongEssayAssessment\EssayTask\Data\WritingSettings;

class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos
    ) {
    }

    public function get() : WritingSettings
    {
        return $this->repos->writingSettings()->one($this->ass_id) ??
            $this->repos->writingSettings()->new()->setAssId($this->ass_id);
    }

    public function validate(WritingSettings $settings) : bool
    {
        return true;
    }

    public function save(WritingSettings $settings) : void
    {
        $this->repos->writingSettings()->save($settings);
    }
}