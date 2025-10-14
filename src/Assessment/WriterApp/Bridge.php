<?php

namespace Edutiek\AssessmentService\Assessment\WriterApp;

use Edutiek\AssessmentService\Assessment\Apps\WriterBridge;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\System\Config\ReadService as ConfigService;
use Edutiek\AssessmentService\System\Data\Config;
use Edutiek\AssessmentService\System\Entity\FullService as EntityService;

class Bridge implements WriterBridge
{
    public function __construct(
        int $ass_id,
        int $user_id,
        private readonly ConfigService $config,
        private readonly EntityService $entity,
        private readonly Repositories $repos,
    ) {
    }

    public function getData(): array
    {
        $config = $this->entity->toPrimitives($this->config->getConfig(), Config::class);

        $data['Config'] = [
            'primary_color' => $config['primary_color'],
            'primary_text_color' => $config['primary_text_color'],
        ];

        return $data;
    }

    public function getUpdate(): array
    {
        return[];
    }
}
