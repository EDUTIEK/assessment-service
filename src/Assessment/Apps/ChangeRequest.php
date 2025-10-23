<?php

namespace Edutiek\AssessmentService\Assessment\Apps;

readonly class ChangeRequest
{
    /**
     * Constructor, see getters for the parameters
     */
    public function __construct(
        private string $type,
        private string $key,
        private int $last_change,
        private ?ChangeAction $action,
        private mixed $payload
    ) {
    }

    /**
     * Type of the entity for which the change should be executed
     * This is component specific
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Key of the entity for which the change was noted in the app
     * This may be a temporary key
     * This should be used as a key for the change response
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Timestamp of the last change (seconds)
     */
    public function getLastChange(): int
    {
        return $this->last_change;
    }

    /**
     * Action to be carried out for the entity with the key
     */
    public function getAction(): ?ChangeAction
    {
        return $this->action;
    }

    /**
     * Action specific payload, e.g. the data to be saved
     */
    public function getPayload(): mixed
    {
        return $this->payload;
    }

    /**
     * Create a response from the request
     */
    public function toResponse(bool $done, mixed $result = null): ChangeResponse
    {
        return new ChangeResponse(
            $this->type,
            $this->key,
            $this->action,
            $done,
            $result
        );
    }
}
