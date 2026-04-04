<?php

namespace Edutiek\AssessmentService\Assessment\Notification;

use Edutiek\AssessmentService\Assessment\Api\CronHandler;
use Edutiek\AssessmentService\Assessment\Api\Internal;
use Edutiek\AssessmentService\Assessment\Api\Dependencies;
use Edutiek\AssessmentService\Assessment\Data\NotificationType;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\System\Data\Result;

class ReviewNotificationsHandler implements CronHandler
{
    public function __construct(
        private int $user_id,
        private Internal $internal,
        private Repositories $repositories
    ) {
    }


    public function run(): Result
    {
        $queue_repo = $this->repositories->notificationQueue();
        $writer_repo = $this->repositories->writer();

        $notify = [];
        foreach ($queue_repo->allByType(NotificationType::WRITER_CORRECTION_FINALIZED) as $entry) {
            $notify[$entry->getAssId()] ??= [];
            $notify[$entry->getAssId()][] = $entry->getUserId();
        }

        foreach ($notify as $ass_id => $user_ids) {
            $orga = $this->internal->orgaSettings($ass_id, $this->user_id);
            if ($orga->reviewPossible()) {
                $service = $this->internal->notification($ass_id, $this->user_id);
                foreach ($writer_repo->allByUserIdsAndAssId($user_ids, $ass_id) as $writer) {
                    $service->sendDirect(NotificationType::WRITER_CORRECTION_FINALIZED, [$writer->getUserId()], $writer);
                }
                $queue_repo->deleteByAssIdAndType($ass_id, NotificationType::WRITER_CORRECTION_FINALIZED);

            } elseif (!$orga->get()->getReviewEnabled()) {
                // review is disabled => pending notifications should no longer be sent
                $queue_repo->deleteByAssIdAndType($ass_id, NotificationType::WRITER_CORRECTION_FINALIZED);
            }
        }

        return new Result(true);
    }
}
