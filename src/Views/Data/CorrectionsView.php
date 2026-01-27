<?php

namespace Edutiek\AssessmentService\Views\Data;

use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\System\Data\UserData;
use Edutiek\AssessmentService\System\Data\UserDisplay;
use Edutiek\AssessmentService\Assessment\Data\Properties;
use Edutiek\AssessmentService\Assessment\Data\Location;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\Task\Data\Settings;

abstract class CorrectionsView
{
    abstract public function getTask(): Settings;
    abstract public function getAssessmentProperties(): Properties;
    abstract public function getWriter(): Writer;
    abstract public function getWriterData(): UserData;
    abstract public function getWriterDisplay(): UserDisplay;
    abstract public function getLocation(): ?Location;
    abstract public function getEssay(): ?Essay;

    /** @return Correction[] */
    abstract public function getCorrections(): array;
    abstract public function getFinalizedByData(): ?UserData;
    abstract public function getAuthorizedByData(): ?UserData;
    abstract public function getExcludedByData(): ?UserData;
}
