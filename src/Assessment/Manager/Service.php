<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Manager;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskInfo;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskManager;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskType;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private LanguageService $language,
        private TaskManager $tasks
    ) {
    }

    /**
     * Create a new assessment with the id given in the constructor of this service
     */
    public function create(bool $multi_tasks): void
    {
        // don't create an assessment twice
        if ($this->repos->orgaSettings()->one($this->ass_id) !== null) {
            return;
        }

        $this->repos->orgaSettings()->save(
            $this->repos->orgaSettings()->new()
            ->setAssId($this->ass_id)
            ->setMultiTasks($multi_tasks)
        );
        $this->repos->correctionSettings()->save(
            $this->repos->correctionSettings()->new()
            ->setAssId($this->ass_id)
        );
        $this->repos->pdfSettings()->save(
            $this->repos->pdfSettings()->new()
            ->setAssId($this->ass_id)
        );

        // create the first task of the assessment
        $this->tasks->create(new TaskInfo(
            $multi_tasks
                ? $this->language->txt('sub_task', ['number' => '1'])
                : $this->language->txt('task'),
            TaskType::ESSAY
        ));
    }

    /**
     * Delete all data of an assessment with the id given in the constructor of this service
     */
    public function delete(): void
    {
        foreach ($this->tasks->all() as $task) {
            $this->tasks->delete($task->getId());
        }

        $this->repos->orgaSettings()->delete($this->ass_id);
        $this->repos->correctionSettings()->delete($this->ass_id);
        $this->repos->pdfSettings()->delete($this->ass_id);

        $this->repos->alert()->deleteByAssId($this->ass_id);
        $this->repos->corrector()->deleteByAssId($this->ass_id);
        $this->repos->gradeLevel()->deleteByAssId($this->ass_id);
        $this->repos->location()->deleteByAssId($this->ass_id);
        $this->repos->logEntry()->deleteByAssId($this->ass_id);
        $this->repos->token()->deleteByAssId($this->ass_id);
        $this->repos->writer()->deleteByAssId($this->ass_id);
    }

    /**
     * Clone all data of an assessment with the id given in the constructor of this service
     */
    public function clone(int $new_ass_id): void
    {
        // clone the settings
        $this->repos->orgaSettings()->save(
            ($this->repos->orgaSettings()->one($this->ass_id) ?? $this->repos->orgaSettings()->new())
            ->setAssId($new_ass_id)
        );
        $this->repos->correctionSettings()->save(
            ($this->repos->correctionSettings()->one($this->ass_id) ?? $this->repos->correctionSettings()->new())
                ->setAssId($new_ass_id)
        );
        $this->repos->pdfSettings()->save(
            ($this->repos->pdfSettings()->one($this->ass_id) ?? $this->repos->pdfSettings()->new())
                ->setAssId($new_ass_id)
        );

        foreach ($this->repos->disabledGroup()->allByAssId($this->ass_id) as $entity) {
            $this->repos->disabledGroup()->save($entity->setAssId($new_ass_id));
        }

        // clone data that is not user related
        foreach ($this->repos->gradeLevel()->allByAssId($this->ass_id) as $entity) {
            $this->repos->gradeLevel()->save($entity->setId(0)->setAssId($new_ass_id));
        }
        foreach ($this->repos->location()->allByAssId($this->ass_id) as $entity) {
            $this->repos->location()->save($entity->setId(0)->setAssId($new_ass_id));
        }

        // clone the tasks
        foreach ($this->tasks->all() as $task) {
            $this->tasks->clone($task->getId(), $new_ass_id);
        }
    }
}
