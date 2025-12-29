<?php

namespace Edutiek\AssessmentService\EssayTask\EssayImport;

class ImportResult
{
    private bool $ok;
    /** @var string[] */
    private array $messages = [];

    public function __construct(
        bool $ok = true,
        ?string $message = null
    ) {
        $this->add($ok, $message);
    }

    public function add(bool $ok, ?string $message): ImportResult
    {
        $this->ok = $ok;
        if ($message) {
            $this->messages[] = $message;
        }
        return $this;
    }

    public function isOk(): bool
    {
        return $this->ok;
    }

    public function hasMessages(): bool
    {
        return !empty($this->messages);
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getMessagesAsHtml(): string
    {
        return implode('<br>', $this->messages);
    }
}
