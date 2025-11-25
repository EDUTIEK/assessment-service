<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\AppBridges;

use Edutiek\AssessmentService\Assessment\Apps\AppCorrectorBridge;
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

class CorrectorBridge implements AppCorrectorBridge
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
        $this->corrector = $this->repos->corrector()->oneByUserIdAndAssId($this->user_id, $this->ass_id);
    }

    public function getData(bool $for_update): array
    {
        $config = $this->config->getConfig();

        $data['Config'] = $this->entity->arrayToPrimitives([
            'primary_color' => $config->getPrimaryColor(),
            'primary_text_color' => $config->getPrimaryTextColor(),
        ]);

        $data['GradeLevels'] = [];
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
            'multiple_correctors' => $settings->hasMultipleCorrectors(),
            'mutual_visibility' => $settings->getMutualVisibility(),
            'procedure_when_distance' => $settings->getProcedureWhenDistance(),
            'procedure' => $settings->getProcedure(),
            'max_auto_distance' => $settings->getMaxAutoDistance(),
            'revision_between' > $settings->getRevisionBetween(),
            'stitch_after_procedure' => $settings->getStitchAfterProcedure(),
            'max_points' => $settings->getMaxPoints(),
            'no_manual_decimals' => $settings->getNoManualDecimals(),
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
        return $change->toResponse(false, 'no changes for assessment');
    }


    public function getItem(int $task_id, int $writer_id): ?array
    {
        // no items handled in assessment component
        return null;
    }
}
