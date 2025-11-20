<?php

namespace Edutiek\AssessmentService\EssayTask\AppBridges;

use DiffMatchPatch\DiffMatchPatch;
use Edutiek\AssessmentService\Assessment\Apps\ChangeAction;
use Edutiek\AssessmentService\Assessment\Apps\ChangeRequest;
use Edutiek\AssessmentService\Assessment\Apps\ChangeResponse;
use Edutiek\AssessmentService\Assessment\Apps\AppBridge;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\EssayTask\Data\WriterNotice;
use Edutiek\AssessmentService\EssayTask\Data\WriterPrefs;
use Edutiek\AssessmentService\EssayTask\Data\WritingStep;
use Edutiek\AssessmentService\EssayTask\Essay\Service as EssayService;
use Edutiek\AssessmentService\System\ConstraintHandling\ResultStatus;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\Entity\FullService as EntityService;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterReadService;
use Edutiek\AssessmentService\Task\Data\WriterAnnotation;
use Edutiek\AssessmentService\Task\Manager\ReadService as TasksService;
use ILIAS\Plugin\LongEssayAssessment\Assessment\Data\Writer;

class WriterBridge implements AppBridge
{
    private ?Writer $writer;
    private $tasks = [];

    public function __construct(
        private int $ass_id,
        private int $user_id,
        private int $service_version,
        private Repositories $repos,
        private EntityService $entity,
        private WriterReadService $writer_service,
        private TasksService $tasks_service,
        private EssayService $essay_service
    ) {
        $this->writer = $this->writer_service->oneByUserId($this->user_id);
        foreach ($this->tasks_service->all() as $task) {
            $this->tasks[$task->getId()] = $task;
        }

    }

    public function getData(bool $for_update): array
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

        if (!$for_update) {
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
                        'task_id' => $task->getId(),
                        'note_no' => $notice->getNoteNo(),
                        'note_text' => $notice->getNoteText(),
                        'last_change' => $notice->getLastChange(),
                    ]);
                }
            }
        }

        return $data;
    }

    public function getFileId(string $entity, int $entity_id): ?string
    {
        return null;
    }

    public function applyChange(ChangeRequest $change): ChangeResponse
    {
        if ($this->writer !== null) {
            switch ($change->getType()) {
                case 'pref':
                    return $this->applyPreferences($change);
                case 'essay':
                    return $this->applyEssay($change);
                case 'note':
                    return $this->applyNote($change);
                case 'step':
                    return $this->applyStep($change);

            }
        }
        return $change->toResponse(false, 'writer or type not found');
    }

    private function applyPreferences(ChangeRequest $change): ChangeResponse
    {
        $repo = $this->repos->writerPrefs();
        $data = $change->getPayload();
        $prefs = $repo->one($this->writer->getId())
            ?? $repo->new()->setWriterId($this->writer->getId());

        $this->entity->fromPrimitives([
            'editor_zoom' => $data['editor_zoom'] ?? null,
            'instructions_zoom' => $data['instructions_zoom'] ?? null,
            'word_count_enabled' => $data['word_count_enabled'] ?? null,
            'word_count_characters' => $data['word_count_characters'] ?? null,
        ], $prefs, WriterPrefs::class);
        $this->entity->secure($prefs, WriterPrefs::class);

        if ($change->getAction() === ChangeAction::SAVE) {
            $repo->save($prefs);
            return $change->toResponse(true);
        }
        return $change->toResponse(false, 'wrong action');
    }

    private function applyEssay(ChangeRequest $change): ChangeResponse
    {
        $repo = $this->repos->essay();
        $data = $change->getPayload();
        $essay = $this->getAndCheckEssay((int) $data['task_id']);
        if ($essay === null) {
            return $change->toResponse(false, 'forbidden');
        }

        if ($change->getAction() === ChangeAction::SAVE) {

            $this->entity->fromPrimitives([
                'written_text' => $data['content'] ?? null,
                'service_version' => $this->service_version,
                'last_change' => $data['last_change'] ?? null,
            ], $essay, Essay::class);
            $this->entity->secure($essay, Essay::class);

            $repo->save($essay);
            return $change->toResponse(true);
        }

        return $change->toResponse(false, 'wrong action');
    }

    private function applyNote(ChangeRequest $change): ChangeResponse
    {
        $data = $change->getPayload();
        $essay = $this->getAndCheckEssay((int) $data['task_id']);
        if ($essay === null) {
            return $change->toResponse(false, 'forbidden');
        }

        $repo = $this->repos->writerNotice();
        $note = $repo->new();
        $this->entity->fromPrimitives([
            'task_id' => $data['task_id'] ?? null,
            'note_no' => $data['note_no'] ?? null,
            'note_text' => $data['note_text'] ?? null,
            'last_change' => $data['last_change'] ?? null,

        ], $note, WriterNotice::class);
        $this->entity->secure($note, WriterNotice::class);

        $found = $repo->oneByEssayIdAndNo($essay->getId(), $note->getNoteNo());

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

    // todo performance optimization needed - essay should not be saved multiple times
    private function applyStep(ChangeRequest $change): ChangeResponse
    {
        //return $change->toResponse(false, 'fake');
        $data = $change->getPayload();
        $essay = $this->getAndCheckEssay((int) $data['task_id']);
        if ($essay === null) {
            return $change->toResponse(false, 'forbidden');
        }

        $step_repo = $this->repos->writingStep();
        $essay_repo = $this->repos->essay();

        $step = $step_repo->new();
        $this->entity->fromPrimitives([
            'essay_id' => $essay->getId(),
            'timestamp' => $data['timestamp'] ?? null,
            'content' => $data['content'] ?? null,
            'is_delta' => $data['is_delta'] ?? null,
            'hash_before' => $data['hash_before'] ?? null,
            'hash_after' => $data['hash_after'] ?? null,

        ], $step, WritingStep::class);
        $this->entity->secure($step, WritingStep::class);

        $dmp = new DiffMatchPatch();
        $currentText = $essay->getWrittenText();
        $currentHash = $essay->getRawTextHash();

        // check if step can be added
        // fault tolerance if a former put was partially applied or the response to the app was lost
        // then this list may include steps that are already saved
        // exclude these steps because they will corrupt the sequence
        // later steps may fit again
        if ($step->getHashBefore() !== $currentHash) {
            if ($step->getIsDelta()) {
                // don't add a delta step that can't be applied
                // step may already be saved, so a later new step may fit
                return($change->toResponse(true));
            } elseif ($step_repo->hasByEssayIdAndHashAfter($essay->getId(), (string) $step->getHashAfter())) {
                // the same full save should not be saved twice
                // note: hash_after is salted by timestamp and is unique
                return($change->toResponse(true));
            }
        }

        if ($step->getIsDelta()) {
            $patches = $dmp->patch_fromText($step->getContent());
            $result = $dmp->patch_apply($patches, $currentText);
            $currentText = $result[0];
        } else {
            $currentText = $step->getContent();
        }
        $currentHash = $step->getHashAfter();

        $step_repo->create($step);
        $essay_repo->save(
            $essay
            ->setWrittenText($currentText)
            ->setRawTextHash((string) $currentHash)
            ->setServiceVersion($this->service_version)
            ->setLastChange($step->getTimestamp())
        );

        return($change->toResponse(true));
    }

    private function getAndCheckEssay(int $task_id): ?Essay
    {
        $essay = $this->essay_service->oneByWriterIdAndTaskId($this->writer->getId(), $task_id);
        if ($essay === null) {
            return null;
        }
        if ($this->essay_service->canChange($essay)->status() !== ResultStatus::OK) {
            return null;
        }
        return $essay;
    }
}
