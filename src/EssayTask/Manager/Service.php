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
                ->setAssId($this->ass_id)
                ->setTaskId($this->task_id)
        );

        // create the task independent writing and correction settings once for an assessment
        if ($this->repos->writingSettings()->one($this->ass_id ?? 0) === null) {
            $this->repos->writingSettings()->save(
                $this->repos->writingSettings()->new()
                ->setAssId($this->ass_id)
            );
        }
    }

    public function delete(): void
    {
        $this->repos->taskSettings()->delete($this->task_id);

        // delete writing and correction setting one the last task is deleted
        if (!$this->repos->taskSettings()->hasByAssId($this->ass_id)) {
            $this->repos->writingSettings()->delete($this->ass_id);
        }

        $this->repos->ratingCriterion()->deleteByTaskId($this->task_id);

        foreach ($this->repos->essay()->allByTaskId($this->task_id) as $essay) {
            if ($essay->getPdfVersion() !== null) {
                $this->storage->deleteFile($essay->getPdfVersion());
            }
            foreach ($this->repos->essayImage()->allByEssayId($essay->getId()) as $image) {
                $this->storage->deleteFile($image->getFileId());
                $this->repos->essayImage()->delete($image->getId());
            }
            $this->repos->writerHistory()->deleteByEssayId($essay->getId());
            $this->repos->writerNotice()->deleteByEssayId($essay->getId());

            $this->repos->essay()->delete($essay->getId());
        }
    }

    public function clone(int $new_ass_id, int $new_task_id): void
    {
        $settings = $this->repos->taskSettings()->one($this->task_id);
        if ($settings !== null && !$this->repos->taskSettings()->one($new_task_id) === null) {
            $this->repos->taskSettings()->save($settings
                ->setAssId($this->ass_id)
                ->setTaskId($new_task_id));
        }

        // clone the task independent writing and correction settings once for an assessment
        if ($this->repos->writingSettings()->one($new_ass_id) === null) {
            $this->repos->writingSettings()->save($this->repos->writingSettings()->one($this->ass_id)
                ->setAssId($new_ass_id));
        }

        // clone the common rating criteria (not belonging to a corrector)
        foreach ($this->repos->ratingCriterion()->allByTaskIdAndCorrectorId($this->task_id, null) as $criterion) {
            $this->repos->ratingCriterion()->save($criterion->setTaskId($new_task_id)->setId(0));
        }
    }
}
