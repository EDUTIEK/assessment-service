<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Task\Settings;

use Edutiek\AssessmentService\Task\Data\Settings;

interface FullService
{
    public function get(): Settings;
    public function validate(Settings $settings): bool;
    public function save(Settings $settings): void;
}