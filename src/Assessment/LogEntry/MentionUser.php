<?php

namespace Edutiek\AssessmentService\Assessment\LogEntry;

use Edutiek\AssessmentService\Assessment\Data\Corrector;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\System\Data\UserData;
use Edutiek\AssessmentService\System\Data\UserDisplay;
use ILIAS\User;

/**
 * To specify which user should be named in the entry, regardless of its origin. If the system ID is available, please use it, as this eliminates the need for querying.
 */
class MentionUser
{
    public function __construct(
        public readonly int $id,
        public readonly UserType $type,
    ) {
    }

    public static function fromCorrector(int|Corrector $obj_or_id): self
    {
        if($obj_or_id instanceof Corrector) {
            return new self($obj_or_id->getUserId(), UserType::System);
        }

        return new self($obj_or_id, UserType::Corrector);
    }

    public static function fromWriter(int|Writer $id): self
    {
        if($id instanceof Writer) {
            return new self($id->getUserId(), UserType::System);
        }

        return new self($id, UserType::Writer);
    }

    public static function fromSystem(int|UserData|UserDisplay $obj_or_id): self
    {
        if($obj_or_id instanceof UserDisplay || $obj_or_id instanceof UserData) {
            $obj_or_id = $obj_or_id->getId();
        }

        return new self($obj_or_id, UserType::System);
    }
}