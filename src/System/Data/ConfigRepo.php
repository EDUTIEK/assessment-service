<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

interface ConfigRepo
{
    public function one(): Config;
    public function save(Config $config): void;
}
