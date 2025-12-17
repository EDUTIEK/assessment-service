<?php

namespace Edutiek\AssessmentService\Views\Data;

use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\System\Data\UserData;
use Edutiek\AssessmentService\System\Data\UserDisplay;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\Assessment\Data\Location;

abstract class WriterView
{
    abstract public function getWriter(): Writer;
    abstract public function getWriterData(): UserData;
    abstract public function getWriterDisplay(): UserDisplay;
    abstract public function getLocation(): ?Location;
    abstract public function getEssayTaskSummary(): EssayTaskSummary;
    abstract public function getAuthorizedByData(): ?UserData;
    abstract public function getExcludedByData(): ?UserData;
}