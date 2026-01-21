<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ConstraintHandling;

readonly class Result
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

    public function status(): ResultStatus
    {
        return $this->status;
    }

    public function messages(): array
    {
        return $this->messages;
    }

}
