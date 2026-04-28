<?php

namespace Edutiek\AssessmentService\Task\CorrectionProcess;

use Edutiek\AssessmentService\Assessment\Data\CorrectionProcedure;
use Edutiek\AssessmentService\Assessment\Data\CorrectionStatus;

enum ProcessStep: int
{
    case FIRST_AUTHORIZATION = 1;
    case SECOND_AUTHORIZATION = 2;
    case FIRST_REVISION = 3;
    case SECOND_REVISION = 4;
    case STITCH_DECISION = 5;


    /**
     * @return ProcessStep[]
     */
    public static function allSteps(): array
    {
        return [
          self::FIRST_AUTHORIZATION->value => self::FIRST_AUTHORIZATION,
          self::SECOND_AUTHORIZATION->value => self::SECOND_AUTHORIZATION,
          self::FIRST_REVISION->value => self::FIRST_REVISION,
          self::SECOND_REVISION->value => self::SECOND_REVISION,
          self::STITCH_DECISION->value => self::STITCH_DECISION,
        ];
    }

    /**
     * @return ProcessStep[]
     */
    public static function availableSteps(bool $multi_correctors = false, bool $with_procedures = false, bool $with_stitch = false): array
    {
        $steps[self::FIRST_AUTHORIZATION->value] = self::FIRST_AUTHORIZATION;

        if ($multi_correctors) {
            $steps[self::SECOND_AUTHORIZATION->value] = self::SECOND_AUTHORIZATION;

            if ($with_procedures) {
                $steps[self::FIRST_REVISION->value] = self::FIRST_REVISION;
                $steps[self::SECOND_REVISION->value] = self::SECOND_REVISION;
            }

            if ($with_stitch) {
                $steps[self::STITCH_DECISION->value] = self::STITCH_DECISION;
            }
        }
        return $steps;
    }

    public function isHigherOrEqualThan($compare_step): bool
    {
        return $this->value >= $compare_step->value;
    }



    public function langVar(bool $multi_correctors = false, ?CorrectionProcedure $procedure = null): string
    {
        switch ($this) {
            case self::FIRST_AUTHORIZATION:
                return $multi_correctors ? 'step_first_authorization' : 'step_authorization';
            case self::SECOND_AUTHORIZATION:
                return $multi_correctors ? 'step_second_authorization' : 'step_authorization';

            case self::FIRST_REVISION:
                return match($procedure) {
                    CorrectionProcedure::APPROXIMATION => 'step_first_approximation',
                    CorrectionProcedure::CONSULTING => 'step_first_consulting',
                    default => 'step_first_revision',
                };
            case self::SECOND_REVISION:
                return match($procedure) {
                    CorrectionProcedure::APPROXIMATION => 'step_second_approximation',
                    CorrectionProcedure::CONSULTING => 'step_second_consulting',
                    default => 'step_second_revision',
                };
            default:
                return 'step_stitch_decision';
        }
    }
}
