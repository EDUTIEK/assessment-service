<?php

namespace Edutiek\AssessmentService\Assessment\Apps;

class ChangeResponse
{
    /**
     * Constructor, see getters for the parameters
     */
    public function __construct(
        private string $type,
        private string $key,
        private ?ChangeAction $action,
        private bool $done,
        private mixed $result,
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
     * Action to be carried out for the entity with the key
     */
    public function getAction(): ?ChangeAction
    {
        return $this->action;
    }

    /**
     * Action was successfully carried out
     * The result may give details
     */
    public function isDone(): bool
    {
        return $this->done;
    }

    /**
     * Result of the action
     * This is component specific
     */
    public function getResult(): mixed
    {
        return $this->result;
    }

    /**
     * Get the properties as array
     */
    public function toArray(): array
    {
        return [
          'type' => $this->type,
          'key' => $this->key,
          'action' => $this->action->value,
          'done' => $this->done,
          'result' => $this->result,
        ];
    }
}
