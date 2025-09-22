<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

interface EssayImageRepo
{
    public function new(): EssayImage;
    public function one(int $id): ?EssayImage;



    public function save(EssayImage $entity): void;
    public function delete(int $id): void;

    /**
     * Get all page images of an essay
     * This should use an atomic query if possible
     * @return EssayImage[]
     */
    public function allByEssayId(int $essay_id): array;

    /**
     * Replace all images of an essay and return the deleted entities
     * This should use an atomic query if possible
     * @param EssayImage[] $images
     * @return EssayImage[]
    */
    public function replaceByEssayId(int $essay_id, array $images): array;

    /**
     * Delete all images of an essay and return the deleted entities
     * This should use an atomic query if possible
     * @return EssayImage[]
     */
    public function deleteByEssayId(int $essay_id): array;
}
