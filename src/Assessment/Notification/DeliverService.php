<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Notification;

use Edutiek\AssessmentService\Assessment\Data\NotificationType;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\Data\Corrector;

interface DeliverService
{
    public function createFor(NotificationType $type, ?Writer $writer = null, ?Corrector $corrector = null): void;

    public function sendDirect(NotificationType $type, array $to_ids, ?Writer $writer): void;

}
