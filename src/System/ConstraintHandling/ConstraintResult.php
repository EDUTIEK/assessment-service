<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ConstraintHandling;

use Edutiek\AssessmentService\System\Data\Result;

readonly class ConstraintResult
{
    /**
     * Result of a contraint check
     * The messages should be short one-liners and already translated for the current user
     *
     * @param ResultStatus $status  status code
     * @param string[] $messages  messages giving details related to the code
     */
    public function __construct(
        private ResultStatus $status,
        private array $messages
    ) {
    }

    public function isOk(): bool
    {
        return $this->status === ResultStatus::OK;
    }

    public function isAsking(): bool
    {
        return $this->status === ResultStatus::ASK;
    }

    public function isBlocked(): bool
    {
        return $this->status === ResultStatus::BLOCK;
    }

    public function status(): ResultStatus
    {
        return $this->status;
    }

    public function messages(): array
    {
        return $this->messages;
    }

    public function toSimpleResult(): Result
    {
        $result = new Result(true);

        switch ($this->status()) {
            case ResultStatus::OK:
                break;
            case ResultStatus::ASK:
                foreach ($this->messages() as $message) {
                    $result->addNote($message);
                }
                break;
            case ResultStatus::BLOCK:
                foreach ($this->messages() as $message) {
                    $result->addFailure($message);
                }
                break;
        }

        return $result;
    }
}
