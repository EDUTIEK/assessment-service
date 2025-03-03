<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Manager;

use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\Language\FullService as Language;

readonly class Service implements \Edutiek\AssessmentService\Task\TypeInterfaces\Manager
{
    public function __construct(
        private int $ass_id,
        private int $task_id,
        private Repositories $repos,
        private Storage $storage,
        private Language $language
    ) {
    }

    public function create(): void
    {
        // don't create a task twice
        if ($this->repos->taskSettings()->one($this->task_id ?? 0) !== null) {
            return;
        }

        $this->repos->taskSettings()->save(
            $this->repos->taskSettings()->new()
            ->setTaskId($this->task_id)
        );

        // create the task independent writing and correction settings once
        if ($this->repos->writingSettings()->one($this->ass_id ?? 0) === null) {
            $this->repos->writingSettings()->save($this->repos->writingSettings()->new()
                ->setAssId($this->ass_id));
        }
        if ($this->repos->correctionSettings()->one($this->ass_id ?? 0) === null) {
            $this->repos->correctionSettings()->save($this->repos->correctionSettings()->new()
                ->setAssId($this->ass_id));
        }
    }

    public function delete(): void
    {
        $this->repos->taskSettings()->delete($this->task_id);

        if (!$this->repos->taskSettings()->hasByAssId($this->ass_id)) {
            $this->repos->writingSettings()->delete($this->ass_id);
            $this->repos->correctionSettings()->delete($this->ass_id);
        }

        $this->repos->correctorTaskPrefs()->deleteByTaskId($this->task_id);
        $this->repos->ratingCriterion()->deleteByTaskId($this->task_id);

        foreach ($this->repos->essay()->allByTaskId($this->task_id) as $essay) {
            foreach ($this->repos->essayImage()->allByEssayId($essay->getId()) as $image) {
                $this->storage->deleteFile($image->getFileId());
                $this->repos->essayImage()->delete($image->getId());
            }
            $this->repos->correctorComment()->deleteByEssayId($essay->getId());
            $this->repos->correctorPoints()->deleteByEssayId($essay->getId());
            $this->repos->correctorSummary()->deleteByEssayId($essay->getId());
            $this->repos->writerHistory()->deleteByEssayId($essay->getId());
            $this->repos->writerNotice()->deleteByEssayId($essay->getId());

            $this->repos->essay()->delete($essay->getId());
        }
    }

    public function clone(int $new_ass_id, int $new_task_id): void
    {
        $settings = $this->repos->taskSettings()->one($this->task_id);
        if ($settings !== null && !$this->repos->taskSettings()->one($new_task_id) === null) {
            $this->repos->taskSettings()->save($settings->setTaskId($new_task_id));
        }

        $writing_settings = $this->repos->writingSettings()->one($this->ass_id);
        if ($writing_settings !== null && !$this->repos->writingSettings()->one($new_ass_id) === null) {
            $this->repos->writingSettings()->save($writing_settings->setAssId($new_ass_id));
        }

        $correction_settings = $this->repos->correctionSettings()->one($this->ass_id);
        if ($correction_settings !== null && !$this->repos->correctionSettings()->one($new_ass_id) === null) {
            $this->repos->correctionSettings()->save($correction_settings->setAssId($new_ass_id));
        }

        foreach ($this->repos->ratingCriterion()->allByTaskIdAndCorrectorId($this->task_id, null) as $criterion) {
            $this->repos->ratingCriterion()->save($criterion->setTaskId($new_task_id)->setId(0));
        }
    }
}
