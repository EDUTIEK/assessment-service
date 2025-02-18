<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Authentication;

use DateTimeImmutable;
use Edutiek\AssessmentService\Assessment\Data\Token;
use Edutiek\AssessmentService\Assessment\Data\TokenPurpose;

interface FullService
{
    /**
     * Create a new validity date for a token purpose
     * A null value means endless validity
     */
    public function newValitity(TokenPurpose $purpose): ?DateTimeImmutable;

    /**
     * Create a new token of a user, assessment and purpose (not yet saved)
     */
    public function newToken(int $user_id, TokenPurpose $purpose): Token;

    /**
     * Get the token of a user, assessment and purpose
     * This is used for the authorization of REST calls
     * Only one valid token should exist for the current user, assessment and purpose
     */
    public function getToken(int $user_id, TokenPurpose $purpose): ?Token;

    /**
     * Save a token
     * It must overwrite an existing token of the user, assessment and purpose
     * This will make an existing token in an already opened frontend invalid
     */
    public function saveToken(Token $token): void;

    /**
     * Check a request signature
     * The signature is created in the web app from user_id context_id and token value
     */
    public function checkSignature(Token $token, string $signature): bool;

    /**
     * Check if the client ip address is allowed for a token
     */
    public function checkRemoteAddress(Token $token): bool;

    /**
     * Check if a token is still valid
     */
    public function checkValidity(Token $token): bool;
}
