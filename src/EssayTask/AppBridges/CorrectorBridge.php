<?php

namespace Edutiek\AssessmentService\EssayTask\AppBridges;

use Edutiek\AssessmentService\Assessment\Apps\AppBridge;
use Edutiek\AssessmentService\Assessment\Apps\AppCorrectorBridge;
use Edutiek\AssessmentService\Assessment\Apps\ChangeAction;
use Edutiek\AssessmentService\Assessment\Apps\ChangeRequest;
use Edutiek\AssessmentService\Assessment\Apps\ChangeResponse;
use Edutiek\AssessmentService\Assessment\CorrectionSettings\ReadService as AssessmentSettingsService;
use Edutiek\AssessmentService\Assessment\Corrector\ReadService as CorrectorReadService;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterReadService;
use Edutiek\AssessmentService\Assessment\Data\Corrector;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\System\Entity\FullService as EntityFullService;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\HtmlProcessing\FullService as HtmlProcessing;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\System\User\ReadService as UserReadService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\ReadService as AssignmentService;
use Edutiek\AssessmentService\Task\Manager\ReadService as TaskService;
use Psr\Http\Message\UploadedFileInterface;

class CorrectorBridge implements AppCorrectorBridge
{
    private $corrector;

    public function __construct(
        private readonly int $ass_id,
        private readonly int $user_id,
        private readonly Repositories $repos,
        private readonly Storage $storage,
        private readonly EntityFullService $entity,
        private readonly CorrectorReadService $corrector_service,
        private readonly WriterReadService $writer_service,
        private readonly AssessmentSettingsService $assesment_settings,
        private readonly AssignmentService $assignment_service,
        private readonly TaskService $task_service,
        private readonly Language $language,
        private readonly UserReadService $user_service,
        private readonly HtmlProcessing $html_processing,
    ) {
        $this->corrector = $this->corrector_service->oneByUserId($this->user_id);
    }

    public function getData(bool $for_update): array
    {
        $data = [];

        $settings = $this->repos->writingSettings()->one($this->ass_id) ??
            $this->repos->writingSettings()->new();

        $data['Settings'] = $this->entity->arrayToPrimitives([
            'headline_scheme' => $settings->getHeadlineScheme(),
        ]);

        return $data;
    }

    public function getItem(int $task_id, int $writer_id): ?array
    {
        $data = [
            'Essay' => [],
            'Pages' => []
        ];

        $essay = $this->repos->essay()->oneByWriterIdAndTaskId(
            $writer_id,
            $task_id,
        );

        if ($essay === null) {
            return [];
        }

        $data['Essay'] = $this->entity->arrayToPrimitives([
            'text' => $this->html_processing->processHtmlForMarking($essay->getWrittenText() ?? ''),
        ]);

        $pages = $this->repos->essayImage()->allByEssayId($essay->getId());
        foreach ($pages as $page) {
            $data['Pages'][] = $this->entity->arrayToPrimitives([
                'id' => $page->getId(),
                'task_id' => $essay->getTaskId(),
                'writer_id' => $essay->getWriterId(),
                'page_no' => $page->getPageNo(),
                'width' => $page->getWidth(),
                'height' => $page->getHeight(),
                'thumb_width' => $page->getThumbWidth(),
                'thumb_height' => $page->getThumbHeight(),
            ]);
        }

        return $data;
    }

    public function getFileId(string $entity, int $entity_id): ?string
    {
        switch ($entity) {
            case 'image':
            case 'thumb':
                $page = $this->repos->essayImage()->one($entity_id);
                $essay = $this->repos->essay()->one($page?->getEssayId());
                if (!$this->task_service->has($essay?->getTaskId() ?? 0)) {
                    return null;
                }
                if ($this->corrector) {
                    $assignment = $this->assignment_service->oneByIds(
                        $essay->getWriterId(),
                        $this->corrector->getId(),
                        $essay->getTaskId()
                    );
                    if ($assignment === null) {
                        return null;
                    }
                }
                return ($entity == 'image' ? $page->getFileId() : $page->getThumbId());
        }
        return null;
    }

    public function applyChanges(string $type, array $changes): array
    {
        if ($this->corrector !== null) {
            switch ($type) {
                default:
                    return array_map(fn(ChangeRequest $change) => $change->toResponse(false, 'wrong type'), $changes);
            }
        }
        return array_map(fn(ChangeRequest $change) => $change->toResponse(false, 'corrector not found'), $changes);
    }

    public function processUploadedFile(UploadedFileInterface $file, int $task_id, int $writer_id): ?string
    {
        return null;
    }
}
