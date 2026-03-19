<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Apps;
use Edutiek\AssessmentService\System\Log\FullService as LogService;

use Psr\Log\LoggerInterface;

class SlimLogger implements LoggerInterface
{
    public function __construct(
        private LogService $log
    ) {
    }

    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->log->error((string) $message);
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->log->error((string) $message);
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->log->error((string) $message);
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->log->error((string) $message);
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->log->error((string) $message);
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->log->info((string) $message);
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->log->info((string) $message);
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->log->debug((string) $message);
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->info((string) $message);
    }
}