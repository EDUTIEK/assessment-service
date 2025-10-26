<?php

namespace Edutiek\AssessmentService\EssayTask\AppBridges;

use Edutiek\AssessmentService\Assessment\Apps\ChangeAction;
use Edutiek\AssessmentService\Assessment\Apps\ChangeRequest;
use Edutiek\AssessmentService\Assessment\Apps\ChangeResponse;
use Edutiek\AssessmentService\Assessment\Apps\WriterBridge as WriterBridgeInterface;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\EssayTask\Data\WriterNotice;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\Entity\FullService as EntityService;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterReadService;
use Edutiek\AssessmentService\Task\Data\WriterAnnotation;
use Edutiek\AssessmentService\Task\Manager\ReadService as TasksService;
use ILIAS\Plugin\LongEssayAssessment\Assessment\Data\Writer;

class WriterBridge implements WriterBridgeInterface
{
    private ?Writer $writer;
    private $tasks = [];

    public function __construct(
        private int $ass_id,
        private int $user_id,
        private Repositories $repos,
        private EntityService $entity,
        private WriterReadService $writer_service,
        private TasksService $tasks_service
    ) {
        $this->writer = $this->writer_service->oneByUserId($this->user_id);
        foreach ($this->tasks_service->all() as $task) {
            $this->tasks[$task->getId()] = $task;
        }

    }

    public function getData(): array
    {
        if ($this->writer === null) {
            return [];
        }

        $data = [];

        $settings = $this->repos->writingSettings()->one($this->ass_id);
        $data['WritingSettings'] = $this->entity->arrayToPrimitives([
            'headline_scheme' => $settings?->getHeadlineScheme(),
            'formatting_options' => $settings?->getFormattingOptions(),
            'notice_boards' => $settings?->getNoticeBoards(),
            'copy_allowed' => $settings?->getCopyAllowed(),
            'allow_spellcheck' => $settings?->getAllowSpellcheck(),
        ]);

        $prefs = $this->repos->writerPrefs()->one($this->writer->getId());
        $data['WriterPrefs'] = $this->entity->arrayToPrimitives([
            'instructions_zoom' => $prefs?->getInstructionsZoom(),
            'editor_zoom' => $prefs?->getEditorZoom(),
            'word_count_enabled' => $prefs?->getWordCountEnabled(),
            'word_count_characters' => $prefs?->getWordCountCharacters(),
        ]);

        foreach ($this->tasks as $task) {
            $essay = $this->repos->essay()->oneByWriterIdAndTaskId($this->writer->getId(), $task->getId());
            if (!$essay) {
                $essay = $this->repos->essay()->new()
                ->setWriterId($this->writer->getId())
                ->setTaskId($task->getId());
                $this->repos->essay()->save($essay);
            }
            $data['Essays'][] = $this->entity->arrayToPrimitives([
                'id' => $essay->getId(),
                'task_id' => $task->getId(),
                'content' => $essay->getWrittenText(),
                'hash' => $essay->getRawTextHash()
            ]);

            foreach ($this->repos->writerNotice()->allByEssayId($essay->getId()) as $notice) {
                $data['WriterNotices'][] = $this->entity->arrayToPrimitives([
                    'id' => $notice->getId(),
                    'task_id' => $task->getId(),
                    'note_no' => $notice->getNoteNo(),
                    'note_text' => $notice->getNoteText(),
                    'last_change' => $notice->getLastChange(),
                ]);
            }
        }

        return $data;
    }

    public function getUpdate(): array
    {
        if ($this->writer === null) {
            return [];
        }

        return [];
    }

    public function getFileId(string $entity, int $entity_id): ?string
    {
        return null;
    }

    public function applyChange(ChangeRequest $change): ChangeResponse
    {
        if ($this->writer !== null) {
            switch ($change->getType()) {
                case 'notes':
                    return $this->applyNotes($change);
            }
        }
        return $change->toResponse(false, 'type not found');
    }


    private function applyEssay(ChangeRequest $change): ChangeResponse
    {
        return $change->toResponse(false, 'wrong action');
    }

    private function applyNotes(ChangeRequest $change): ChangeResponse
    {
        $repo = $this->repos->writerNotice();

        $note = $repo->new();
        $data = $change->getPayload();

        $this->entity->fromPrimitives([
            'task_id' => $data['task_id'] ?? null,
            'note_no' => $data['note_no'] ?? null,
            'note_text' => $data['note_text'] ?? null,
            'last_change' => $data['last_change'] ?? null,

        ], $note, WriterNotice::class);
        $this->entity->secure($note, WriterNotice::class);

        $essay = $this->repos->essay()->oneByWriterIdAndTaskId($this->writer->getId(), (int) $data['task_id']);
        if ($essay === null) {
            return $change->toResponse(false, 'wrong task');
        }

        $found = $this->repos->writerNotice()->oneByEssayIdAndNo($essay->getId(), $note->getNoteNo());

        switch ($change->getAction()) {
            case ChangeAction::SAVE:
                $note->setId($found?->getId() ?? 0);
                $note->setEssayId($essay->getId());
                $repo->save($note);
                return $change->toResponse(true);

            case ChangeAction::DELETE:
                if ($found) {
                    $repo->delete($found);
                }
                return $change->toResponse(true);
        }

        return $change->toResponse(false, 'wrong action');
    }
}
