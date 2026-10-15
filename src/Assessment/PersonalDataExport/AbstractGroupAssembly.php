<?php

namespace Edutiek\AssessmentService\Assessment\PersonalDataExport;

use Edutiek\AssessmentService\Assessment\PersonalDataExport\Attributes\Factory as AttributeFactory;

abstract class AbstractGroupAssembly
{
    protected AttributeFactory $attribute_factory;
    protected Group $group;

    public function __construct(protected UsageScope $usage_scope)
    {
        $this->group = $this->createGroup();
        $this->attribute_factory = new AttributeFactory();
    }

    abstract public function createGroup(): Group;

    public function addSubGroup(Group $group): void
    {
        $this->group->addSubGroup($group);
    }

    public function getGroup(): Group
    {
        return $this->group;
    }

    /**
     * Anonymize writer ID based on scope
     * If scope has Corrector Id and user id: anonymize every writer Id which is different
     * If scope has writer id: anonymize every writer id not in scope
     */
    protected function anonymizeWriter(int $writer_id): string
    {
        // If scope has writer id, only anonymize if it's different from the scope's writer id
        if ($this->usage_scope->getWriterId() === $writer_id) {
            return "You";
        }
        $hash = substr(sha1('gdpr-writer-anon:' . $writer_id), 0, 8);
        return "Writer $hash";
    }

    /**
     * Anonymize corrector ID based on scope
     * If scope has Corrector Id and user id: anonymize every corrector Id which is different
     * If scope has writer id: anonymize every corrector id
     */
    protected function anonymizeCorrector(int $corrector_id): string
    {
        // If scope has writer id, anonymize all corrector ids
        if ($this->usage_scope->getCorrectorId() === $corrector_id) {
            return "You";
        }

        $hash = substr(sha1('gdpr-corrector-anon:' . $corrector_id), 0, 8);
        return "Corrector $hash";
    }

    /**
     * Anonymize user ID based on scope
     */
    protected function anonymizeUser(int $user_id): string
    {
        // If scope has writer id, anonymize all user ids
        if ($this->usage_scope->getUserId() === $user_id) {
            return "You";
        }

        $hash = substr(sha1('gdpr-user-anon:' . $user_id), 0, 8);
        return "User $hash";
    }
}
