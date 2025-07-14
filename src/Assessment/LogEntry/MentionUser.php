<?php

namespace Edutiek\AssessmentService\Assessment\LogEntry;

class MentionUser
{
    public function __construct(
        public readonly int $id,
        public readonly UserType $type,
    ) {
    }

    public static function fromCorrector(int $id): self
    {
        return new self($id, UserType::Corrector);
    }

    public static function fromWriter(int $id): self
    {
        return new self($id, UserType::Writer);
    }

    public static function fromSystem(int $id): self
    {
        return new self($id, UserType::System);
    }
}