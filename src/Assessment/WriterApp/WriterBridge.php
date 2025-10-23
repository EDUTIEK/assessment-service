<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WriterApp;

use Edutiek\AssessmentService\Assessment\Apps\ChangeRequest;
use Edutiek\AssessmentService\Assessment\Apps\ChangeResponse;
use Edutiek\AssessmentService\Assessment\Apps\WriterBridge as WriterBridgeInterface;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\WorkingTime\Factory as WorkingTimeFactory;
use Edutiek\AssessmentService\System\Config\ReadService as ConfigService;
use Edutiek\AssessmentService\System\Data\Config;
use Edutiek\AssessmentService\System\Entity\FullService as EntityService;

class WriterBridge implements WriterBridgeInterface
{
    public function __construct(
        private readonly int $ass_id,
        private readonly int $user_id,
        private readonly WorkingTimeFactory $working_time_factory,
        private readonly ConfigService $config,
        private readonly EntityService $entity,
        private readonly Repositories $repos,
    ) {
    }

    public function getData(): array
    {
        $data = [];

        $config = $this->config->getConfig();
        $data['Config'] = $this->entity->arrayToPrimitives([
            'primary_color' => $config->getPrimaryColor(),
            'primary_text_color' => $config->getPrimaryTextColor(),
        ]);

        $writer = $this->repos->writer()->oneByUserIdAndAssId($this->user_id, $this->ass_id);
        if ($writer !== null) {
            $working_time = $this->working_time_factory->workingTime(
                $this->repos->orgaSettings()->one($this->ass_id),
                $writer
            );

            $data['Writer'] = $this->entity->arrayToPrimitives([
               'id' => $writer->getId(),
               'writer_name' => $writer->getPseudonym(),
               'working_start' => $working_time->getWorkingStart(),
               'working_deadline' => $working_time->getWorkingDeadline(),
               'is_authorized' => $writer->isAuthorized(),
               'is_excluded' => $writer->isExcluded(),
            ]);

            foreach ($this->repos->alert()->allByAssIdAndWriterId($this->ass_id, $writer->getId()) as $alert) {
                $data['Alerts'][] = $this->entity->arrayToPrimitives([
                    'id' => $alert->getId(),
                    'time' => $alert->getShownFrom(),
                    'message' => $alert->getMessage(),
                ]);
            }
        }

        return $data;
    }

    public function getUpdate(): array
    {
        return[];
    }

    public function getFileId(string $entity, int $entity_id): ?string
    {
        return null;
    }

    public function applyChange(ChangeRequest $change): ChangeResponse
    {
        return $change->toResponse(false);
    }
}
