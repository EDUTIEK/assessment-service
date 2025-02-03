<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface OrgaSettingsRepo
{
    public function new(): OrgaSettings;
    public function one(int $ass_id): ?OrgaSettings;
    public function save(OrgaSettings $entity): void;
    public function delete(int $ass_id): void;
}
