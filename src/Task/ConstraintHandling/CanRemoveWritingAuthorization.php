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
use Edutiek\AssessmentService\System\ConstraintHandling\Actions\RemoveWritingAuthorization;

/**
 * CCheck if content of an essay (text or pdf) can be changed
 * - BLOCK if authorized corrections exist
 */
class CanRemoveWritingAuthorization implements Constraint
{
    public function __construct(
        private Repositories $repos,
        private LanguageService $language,
    ) {
    }

    public static function actions(): array
    {
        return[RemoveWritingAuthorization::class];
    }

    /**
     * BLOCK the removing of a writing authorization when the correction status is not open
     *
     * @param RemoveWritingAuthorization $action
     */
    public function check(Action $action, ResultCollection $results): void
    {
        foreach ($this->repos->correctorSummary()->allByTaskIdAndWriterId($action->getTaskId(), $action->getWriterId()) as $summary) {
            if ($summary->isAuthorized()) {
                $results->add(new ConstraintResult(ResultStatus::BLOCK, [
                    $this->language->txt('correction_prevents_remove_of_writing_authorization')
                ]));
            }
        }
    }
}
