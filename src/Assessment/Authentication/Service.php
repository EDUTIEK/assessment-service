<?php

namespace Edutiek\AssessmentService\Assessment\Authentication;

use DateTimeImmutable;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\Token;
use Edutiek\AssessmentService\Assessment\Data\TokenPurpose;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private int $context_id,
        private Repositories $repos
    ) {
    }

    public function newValitity(TokenPurpose $purpose): ?DateTimeImmutable
    {
        switch ($purpose) {
            case TokenPurpose::FILE:
            case TokenPurpose::DATA:
                return null;                   // todo: temporary solution for both until re-authentication is possible
        }

        return null;
    }

    public function newToken(int $user_id, TokenPurpose $purpose): Token
    {
        // generate a random uuid like string for the token
        $value = sprintf(
            '%04x%04x%04x%04x%04x%04x%04x%04x',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535)
        );

        return $this->repos->token()->new()
            ->setAssId($this->ass_id)
            ->setUserId($user_id)
            ->setToken($value)
            ->setIp($_SERVER['REMOTE_ADDR'])
            ->setPurpose($purpose)
            ->setValidUntil($this->newValitity($purpose));
    }

    public function getToken(int $user_id, TokenPurpose $purpose): ?Token
    {
        return $this->repos->token()->oneByIdsAndPurpose($user_id, $this->ass_id, $purpose);
    }

    public function saveToken(Token $token): void
    {
        $this->repos->token()->deleteByIdsAndPurpose($token->getUserId(), $token->getAssId(), $token->getPurpose());
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
}
