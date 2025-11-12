<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\AppBridges;

use Edutiek\AssessmentService\Assessment\Apps\ChangeAction;
use Edutiek\AssessmentService\Assessment\Apps\ChangeRequest;
use Edutiek\AssessmentService\Assessment\Apps\ChangeResponse;
use Edutiek\AssessmentService\Assessment\Apps\AppBridge;
use Edutiek\AssessmentService\Assessment\Data\Corrector;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\WorkingTime\Factory as WorkingTimeFactory;
use Edutiek\AssessmentService\Assessment\Writer\FullService as WriterService;
use Edutiek\AssessmentService\System\Config\ReadService as ConfigService;
use Edutiek\AssessmentService\System\Data\Config;
use Edutiek\AssessmentService\System\Entity\FullService as EntityService;
use Edutiek\AssessmentService\Task\Data\ResourceAvailability;
use Edutiek\AssessmentService\Task\Data\ResourceType;
use Edutiek\AssessmentService\Task\Data\Settings;

class CorrectorBridge implements AppBridge
{
    private ?Corrector $corrector;

    private $tasks = [];
    private $resources = [];

    public function __construct(
        private readonly int $ass_id,
        private readonly int $user_id,
        private readonly ConfigService $config,
        private readonly EntityService $entity,
        private readonly Repositories $repos,
    ) {
        $this->corrector = $this->repos->writer()->oneByUserIdAndAssId($this->user_id, $this->ass_id);
    }

    public function getData(bool $for_update): array
    {
        $data = [
            'Config' => [],
            'GradeLevels' => [],
            'Settings' => []
        ];

        $config = $this->config->getConfig();
        $data['Config'] = $this->entity->arrayToPrimitives([
            'primary_color' => $config->getPrimaryColor(),
            'primary_text_color' => $config->getPrimaryTextColor(),
        ]);

        foreach ($this->repos->gradeLevel()->allByAssId($this->ass_id) as $level) {
            $data['GradeLevels'][] = $this->entity->arrayToPrimitives([
                'id' => $level->getId(),
                'min_points' => $level->getMinPoints(),
                'title' => $level->getGrade(),
                'statement' => $level->getStatement(),
            ]);
        }

        $settings = $this->repos->correctionSettings()->one($this->ass_id);
        $data['Settings'] = $this->entity->arrayToPrimitives([
           'mutual_visibility' => $settings->getMutualVisibility(),
           'no_manual_decimals' => $settings->getNoManualDecimals(),
           'procedure' => $settings->getProcedure(),
           'procedure_when_decimals' => $settings->getProcedureWhenDecimals(),
           'procedure_when_distance' => $settings->getProcedureWhenDistance(),
           'max_auto_distance' => $settings->getMaxAutoDistance(),
           'approximation' => $settings->getApproximation(),
           'revision_between' > $settings->getRevisionBetween(),
           'stitch_after_procedure' => $settings->getStitchAfterProcedure(),
        ]);

        return $data;
    }


    public function getFileId(string $entity, int $entity_id): ?string
    {
        // no files handled in assessment component
        return null;
    }

    public function applyChange(ChangeRequest $change): ChangeResponse
    {
        return $change->toResponse(false, 'corrector type not found');
    }


}
