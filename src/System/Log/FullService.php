<?php

namespace Edutiek\AssessmentService\System\Log;

interface FullService
{
    public function error(string $message): void;
    public function info(string $message): void;
    public function debug(string $message): void;
}