<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\ConstraintHandling;

use Edutiek\AssessmentService\Assessment\Writer\FullService as WriterService;
use Edutiek\AssessmentService\System\ConstraintHandling\Action;
use Edutiek\AssessmentService\System\ConstraintHandling\Actions\ChangeWritingContent;
use Edutiek\AssessmentService\System\ConstraintHandling\Constraint;
use Edutiek\AssessmentService\System\ConstraintHandling\Result;
use Edutiek\AssessmentService\System\ConstraintHandling\ResultCollection;
use Edutiek\AssessmentService\System\ConstraintHandling\ResultStatus;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;

class CanChangeWritingContent implements Constraint
{
    public function __construct(
        private WriterService $writer_service,
        private LanguageService $language,
    ) {
    }

    public static function actions(): array
    {
        return[ChangeWritingContent::class];
    }

    /**
     * @param ChangeWritingContent $action
     */
    public function check(Action $action, ResultCollection $results): void
    {
        $writer = $this->writer_service->oneByWriterId($action->getWriterId());
        if ($writer->isAuthorized()) {
            $results->add(new Result($action->isAdmin() ? ResultStatus::ASK : ResultStatus::BLOCK, [
                $this->language->txt('writing_is_already_authorized')
            ]));
        }
        if ($writer->isCorrectionFinalized()) {
            $results->add(new Result($action->isAdmin() ? ResultStatus::ASK : ResultStatus::BLOCK, [
                $this->language->txt('correction_is_already_finalized')
            ]));
        }
    }
}
