<?php

namespace Edutiek\AssessmentService\EssayTask\EssayImport;

/**
 * Info about a file from the essay import
 */
class ImportFile
{
    // basic file propeties
    private string $file_name = '';
    private string $mime_type = '';
    private string $hash = '';
    private string $temp_id = '';

    // assignment and check results
    private string $id = '';
    private string $login = '';
    private bool $hash_ok = false;
    private bool $relevant = false;
    private bool $existing = false;
    private ?int $user_id = null;

    // messages
    private array $errors = [];
    private array $comments = [];


    public function getFileName(): string
    {
        return $this->file_name;
    }

    public function setFileName(string $file_name): ImportFile
    {
        $this->file_name = $file_name;
        return $this;
    }

    public function getMimeType(): string
    {
        return $this->mime_type;
    }

    public function setMimeType(string $mime_type): ImportFile
    {
        $this->mime_type = $mime_type;
        return $this;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): ImportFile
    {
        $this->hash = $hash;
        return $this;
    }

    public function getTempId(): string
    {
        return $this->temp_id;
    }

    public function setTempId(string $temp_id): ImportFile
    {
        $this->temp_id = $temp_id;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): ImportFile
    {
        $this->id = $id;
        return $this;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): ImportFile
    {
        $this->login = $login;
        return $this;
    }

    public function isHashOk(): bool
    {
        return $this->hash_ok;
    }

    public function setHashOk(bool $hash_ok): ImportFile
    {
        $this->hash_ok = $hash_ok;
        return $this;
    }

    public function isRelevant(): bool
    {
        return $this->relevant;
    }

    public function setRelevant(bool $relevant): ImportFile
    {
        $this->relevant = $relevant;
        return $this;
    }

    public function isExisting(): bool
    {
        return $this->existing;
    }

    public function setExisting(bool $existing): ImportFile
    {
        $this->existing = $existing;
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(?int $user_id): ImportFile
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(string $error): ImportFile
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    public function addComment(string $comment): ImportFile
    {
        $this->comments[] = $comment;
        return $this;
    }

    public function isImportPossible(): bool
    {
        return $this->isRelevant() && empty($this->errors);
    }
}
