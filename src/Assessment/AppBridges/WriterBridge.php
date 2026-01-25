<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\AppBridges;

use Edutiek\AssessmentService\Assessment\Apps\ChangeAction;
use Edutiek\AssessmentService\Assessment\Apps\ChangeRequest;
use Edutiek\AssessmentService\Assessment\Apps\ChangeResponse;
use Edutiek\AssessmentService\Assessment\Apps\AppBridge;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\WorkingTime\Factory as WorkingTimeFactory;
use Edutiek\AssessmentService\Assessment\Writer\FullService as WriterService;
use Edutiek\AssessmentService\System\Config\ReadService as ConfigService;
use Edutiek\AssessmentService\System\Data\Config;
use Edutiek\AssessmentService\System\Entity\FullService as EntityService;

class WriterBridge implements AppBridge
{
    private const CHANGE_TYPE_WRITER = 'writer';

    private ?\Edutiek\AssessmentService\Assessment\Data\Writer $writer;

    public function __construct(
        private readonly int $ass_id,
        private readonly int $user_id,
        private readonly WorkingTimeFactory $working_time_factory,
        private readonly WriterService $writer_service,
        private readonly ConfigService $config,
        private readonly EntityService $entity,
        private readonly Repositories $repos,
    ) {
        $this->writer = $this->repos->writer()->oneByUserIdAndAssId($this->user_id, $this->ass_id);
    }

    public function getData(bool $for_update): array
    {
        if ($this->writer === null) {
            return [];
        }

        $data = [];

        $config = $this->config->getConfig();
        $data['Config'] = $this->entity->arrayToPrimitives([
            'primary_color' => $config->getPrimaryColor(),
            'primary_text_color' => $config->getPrimaryTextColor(),
        ]);

        if ($this->writer !== null) {
            $working_time = $this->working_time_factory->workingTime(
                $this->repos->orgaSettings()->one($this->ass_id),
                $this->writer
            );

            $data['Writer'] = $this->entity->arrayToPrimitives([
               'writer_name' => $this->writer->getPseudonym(),
               'working_start' => $working_time->getWorkingStart(),
               'working_deadline' => $working_time->getWorkingDeadline(),
               'is_authorized' => $this->writer->isAuthorized(),
               'is_excluded' => $this->writer->isExcluded(),
            ]);

            foreach ($this->repos->alert()->allByAssIdAndWriterId($this->ass_id, $this->writer->getId()) as $alert) {
                $data['Alerts'][] = $this->entity->arrayToPrimitives([
                    'id' => $alert->getId(),
                    'time' => $alert->getShownFrom(),
                    'message' => $alert->getMessage(),
                ]);
            }
        }

        return $data;
    }


    public function getFileId(string $entity, int $entity_id): ?string
    {
        // no files handled in assessment component
        return null;
    }

    public function applyChanges(string $type, array $changes): array
    {
        if ($type = self::CHANGE_TYPE_WRITER) {
            return array_map(fn(ChangeRequest $change) => $this->applyWriter($change), $changes);
        }
        return array_map(fn(ChangeRequest $change) => $change->toResponse(false, 'wrong type'), $changes);
    }

    public function applyWriter(ChangeRequest $change): ChangeResponse
    {
        if ($change->getAction() === ChangeAction::SAVE) {
            $data = $change->getPayload();
            if ($data['is_authorized'] ?? false) {
                $this->writer_service->authorizeWriting($this->writer, false);
                return $change->toResponse(true, ['is_authorized' => true]);
            }
        }
        return $change->toResponse(false, 'wrong action');
    }
}
