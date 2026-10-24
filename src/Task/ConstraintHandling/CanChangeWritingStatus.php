<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\ConstraintHandling;

use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\System\ConstraintHandling\Action;
use Edutiek\AssessmentService\System\ConstraintHandling\Constraint;
use Edutiek\AssessmentService\System\ConstraintHandling\ConstraintResult;
use Edutiek\AssessmentService\System\ConstraintHandling\ResultCollection;
use Edutiek\AssessmentService\System\ConstraintHandling\ResultStatus;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\System\ConstraintHandling\Actions\ChangeWritingStatus;

/**
 * Check if the status of a writing can be changed
 * - BLOCK if authorized corrections exist
 * - ASK if open correction data exists
 */
readonly class CanChangeWritingStatus implements Constraint
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private LanguageService $language,
    ) {
    }

    public static function actions(): array
    {
        return[ChangeWritingStatus::class];
    }

    /**
     * BLOCK the changing of a writing text to PDF when the correction summary is authorized
     * ASK if unauthorzed correction data exists
     *
     * @param ChangeWritingStatus $action
     */
    public function check(Action $action, ResultCollection $results): void
    {
        if ($this->repos->correctorSummary()->hasAuthorizedByAssId($this->ass_id, $action->getWriterId(), null)) {
            $results->add(new ConstraintResult(ResultStatus::BLOCK, [
                $this->language->txt('constraint_block_correction_is_authorized')
            ]));
        }

        // chech summary, comments or points has data
        if ($this->repos->correctorSummary()->hasDataByWriterId($action->getWriterId())
            || $this->repos->correctorComment()->hasByWriterId($action->getWriterId())
            || $this->repos->correctorPoints()->hasByWriterId($action->getWriterId())
        ) {
            $results->add(new ConstraintResult(ResultStatus::ASK, [
                $this->language->txt('constraint_ask_correction_has_data')
            ]));
        }
    }
}
