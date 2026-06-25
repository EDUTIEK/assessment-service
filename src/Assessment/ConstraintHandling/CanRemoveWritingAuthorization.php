<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\ConstraintHandling;

use Edutiek\AssessmentService\Assessment\Writer\FullService as WriterService;
use Edutiek\AssessmentService\System\ConstraintHandling\Action;
use Edutiek\AssessmentService\System\ConstraintHandling\Constraint;
use Edutiek\AssessmentService\System\ConstraintHandling\ConstraintResult;
use Edutiek\AssessmentService\System\ConstraintHandling\ResultCollection;
use Edutiek\AssessmentService\System\ConstraintHandling\ResultStatus;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\System\ConstraintHandling\Actions\RemoveWritingAuthorization;

/**
 * Check if the authorization of a writing can be removed
 * - BLOCK if the correction process is not open
 */
class CanRemoveWritingAuthorization implements Constraint
{
    public function __construct(
        private WriterService $writer_service,
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
        $writer = $this->writer_service->oneByWriterId($action->getWriterId());
        if (!$writer->isCorrectionOpen()) {
            $results->add(new ConstraintResult(ResultStatus::BLOCK, [
                $this->language->txt('correction_prevents_remove_of_writing_authorization')
            ]));
        }
    }
}
