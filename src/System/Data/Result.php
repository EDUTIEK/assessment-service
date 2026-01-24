<?php

namespace Edutiek\AssessmentService\System\Data;

/**
 * Simple Result of an action or validation
 *
 */
class Result
{
    private bool $ok = true;

    /** @var string[]  */
    private array $failures = [];

    /** @var string[]  */
    private array $notes = [];


    /**
     * Constructor
     * @param ?bool $ok result status
     * @param ?string $message translated note or failure message
     */
    public function __construct(?bool $ok = null, ?string $message = null)
    {
        if (isset($ok)) {
            $this->ok = $ok;
        }

        if (isset($message)) {
            if ($this->ok) {
                $this->addNote($message);
            } else {
                $this->addFailure($message);
            }
        }
    }

    /**
     * Check if no failure happened
     */
    public function isOk(): bool
    {
        return $this->ok;
    }

    /**
     * Check if a failure happened
     */
    public function isFailed(): bool
    {
        return !$this->ok;
    }

    /**
     * Add a translates note
     */
    public function addNote(string $note): void
    {
        $this->notes[] = $note;
    }

    /**
     * Add a translated failure messate
     */
    public function addFailure(?string $message): void
    {
        $this->ok = false;
        if ($message !== null) {
            $this->failures[] = $message;
        }
    }

    /**
     * Get the notes
     * @return string[]
     */
    public function notes(): array
    {
        return $this->notes;
    }

    /**
     * Get the failure messages
     * @return string[]
     */
    public function failures(): array
    {
        return $this->failures;
    }
}