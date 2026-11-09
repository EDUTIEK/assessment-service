<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

use DateTimeImmutable;

interface TokenRepo
{
    /** @return Token[] */
    public function allByUserId(int $user_id): array;
    public function new(int $user_id): Token;
    public function oneByToken(string $token): ?Token;
    public function save(Token $entity): void;
    public function delete(int $id): void;
    public function deleteByUserId(int $user_id): void;
}
