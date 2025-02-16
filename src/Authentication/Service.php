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
        private int $user_id,
        private Repositories $repos,
    ) {
    }

    /**
     * Get a new expire time for a token
     */
    public function getTokenExpireTime(Purpose $purpose): ?DateTimeImmutable
    {
        switch ($purpose) {
            case Purpose::PURPOSE_FILE:
            case Purpose::PURPOSE_DATA:
                return null;                   // todo: temporary solution until re-authentication is possible
        }

        return null;
    }

    /**
     * Generate a new token
     */
    public function generateApiToken(Purpose $purpose): Token
    {
        // generate a random uuid like string for the token
        $value = sprintf('%04x%04x%04x%04x%04x%04x%04x%04x',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535));

        return $this->repos->token()->new()
            ->setAssId($this->ass_id)
            ->setUserId($this->user_id)
            ->setToken($value)
            ->setIp($_SERVER['REMOTE_ADDR'])
            ->setValidUntil($this->getTokenExpireTime($purpose));
    }

    /**
     * Check a request signature
     */
    public function checkSignature(Token $token, string $signature) : bool
    {
        return (md5($this->user_id . $this->context_id . $token->getToken()) == $signature);
    }

    /**
     * Check if the client ip address is allowed for a token
     */
    public function checkRemoteAddress(Token $token) : bool
    {
        return ($token->getIp() == $_SERVER['REMOTE_ADDR']);
    }

    /**
     * Check if a token is still valid
     */
    public function checkTokenValid(Token $token) : bool
    {
        return $token->getValidUntil() === null || $token->getValidUntil()->getTimestamp() > time();
    }
}