<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

interface EssayImageRepo
{
    public function new(): EssayImage;
    public function one(int $id): ?EssayImage;
    /** @return EssayImage[] */
    public function allByEssayId(int $essay_id): array;
    public function save(EssayImage $entity): void;
    public function delete(int $id): void;
    public function deleteByEssayId(int $essay_id): void;
}