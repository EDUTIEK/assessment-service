<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Api;

use DateTimeZone;
use Edutiek\AssessmentService\System\Config\FullService as ConfigFullService;
use Edutiek\AssessmentService\System\Entity\FullService as EntityFullService;
use Edutiek\AssessmentService\System\File\Delivery;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\Format\FullService as FormatFullService;
use Edutiek\AssessmentService\System\HtmlProcessing\FullService as HtmlProcessingFullService;
use Edutiek\AssessmentService\System\Spreadsheet\FullService as SpreadsheetService;
use Edutiek\AssessmentService\System\Transform\FullService as TransformFullService;
use Edutiek\AssessmentService\System\User\ReadService as UserReadService;

readonly class ForClients
{
    public function __construct(
        private Dependencies $dependencies,
        private Internal $internal
    ) {
    }

    public function config(): ConfigFullService
    {
        return $this->internal->config();
    }

    public function entity(): EntityFullService
    {
        return $this->internal->entity();
    }

    public function fileStorage(): Storage
    {
        return $this->dependencies->fileStorage();
    }

    public function fileDelivery(): Delivery
    {
        return $this->dependencies->fileDelivery();
    }

    public function htmlProcessing(): HtmlProcessingFullService
    {
        return $this->internal->htmlProcessing();
    }


    public function tempStorage(): Storage
    {
        return $this->dependencies->tempStorage();
    }

    public function tempDelivery(): Delivery
    {
        return $this->dependencies->tempDelivery();
    }

    public function format(int $user_id, ?DateTimeZone $timezone = null): FormatFullService
    {
        return $this->internal->format($user_id, $timezone);
    }

    public function transform(): TransformFullService
    {
        return $this->internal->transform();
    }

    public function user(): UserReadService
    {
        return $this->internal->user();
    }

    public function spreadsheet(bool $temporary): SpreadsheetService
    {
        return $this->internal->spreadsheet($temporary);
    }
}
