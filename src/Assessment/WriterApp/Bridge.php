<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WriterApp;

use Edutiek\AssessmentService\Assessment\Apps\WriterBridge;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\WorkingTime\Factory as WorkingTimeFactory;
use Edutiek\AssessmentService\System\Config\ReadService as ConfigService;
use Edutiek\AssessmentService\System\Data\Config;
use Edutiek\AssessmentService\System\Entity\FullService as EntityService;

class Bridge implements WriterBridge
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
               'writing_end' => $working_time->getWorkingDeadline(),
                'started' => $working_time->getWorkingStart(),
                'authorized' => $writer->isAuthorized(),
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
}
