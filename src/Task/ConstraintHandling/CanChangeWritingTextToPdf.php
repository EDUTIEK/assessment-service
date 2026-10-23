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
use Edutiek\AssessmentService\System\ConstraintHandling\Actions\ChangeWritingTextToPdf;

/**
 * Check if content of an essay (text or pdf) can be changed
 * - BLOCK if authorized corrections exist
 */
readonly class CanChangeWritingTextToPdf implements Constraint
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private LanguageService $language,
    ) {
    }

    public static function actions(): array
    {
        return[ChangeWritingTextToPdf::class];
    }

    /**
     * BLOCK the changing of a writing text to PDF when the correction summary is authorized
     * ASK if unauthorzed correction data exists
     *
     * @param ChangeWritingTextToPdf $action
     */
    public function check(Action $action, ResultCollection $results): void
    {
        if ($this->repos->correctorSummary()->hasAuthorizedByAssId($this->ass_id, $action->getWriterId(), null)) {
            $results->add(new ConstraintResult(ResultStatus::BLOCK, [
                $this->language->txt('text_to_pdf_correction_is_authorized')
            ]));
        }

        // chech summary, comments or points has data
        if ($this->repos->correctorSummary()->hasDataByWriterId($action->getWriterId())
            || $this->repos->correctorComment()->hasByWriterId($action->getWriterId())
            || $this->repos->correctorPoints()->hasByWriterId($action->getWriterId())
        ) {
            $results->add(new ConstraintResult(ResultStatus::ASK, [
                $this->language->txt('text_to_pdf_correction_has_data')
            ]));
        }
    }
}
