<?php

namespace Edutiek\AssessmentService\Assessment\Authentication;

use DateTimeImmutable;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\Token;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private int $context_id,
        private Repositories $repos
    ) {
    }

    public function newValitity(): ?DateTimeImmutable
    {
        return null;
    }

    public function newToken(int $user_id): Token
    {
        return $this->repos->token()->new()
            ->setAssId($this->ass_id)
            ->setUserId($user_id)
            ->setToken(bin2hex(random_bytes(16)))
            ->setIp($_SERVER['REMOTE_ADDR'])
            ->setValidUntil($this->newValitity());
    }

    public function getByValue(string $value): ?Token
    {
        return $this->repos->token()->oneByToken($value);
    }

    public function getToken(int $user_id): ?Token
    {
        return $this->repos->token()->oneByUserAndAssId($user_id, $this->ass_id);
    }

    public function getTokenByName(string $token): ?Token
    {
        return $this->repos->token()->oneByToken($token);
    }

    public function saveToken(Token $token): void
    {
        $this->repos->token()->deleteByUserAndAssId($token->getUserId(), $token->getAssId());
        $this->repos->token()->save($token);
    }

    public function checkSignature(Token $token, $signature): bool
    {
        return md5($token->getUserId() . $token->getAssId() . $this->context_id . $token->getToken()) == $signature;
    }

    public function checkRemoteAddress(Token $token): bool
    {
        return $token->getIp() == $_SERVER['REMOTE_ADDR'];
    }

    public function checkValidity(Token $token): bool
    {
        return $token->getValidUntil() === null || $token->getValidUntil()->getTimestamp() > time();
    }

    public function deleteToken(int $user_id): void
    {
        $this->repos->token()->deleteByUserAndAssId($user_id, $this->ass_id);
    }
}
