<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\User;

interface AuthService extends ReadService
{
    /**
     * Authenticate a user with given credentials
     * - Check username, password, and second factor
     * - Check max failed login attempts and deactivate user of exceeded
     * - Check activation
     * - Return the user data, if successfully authentified
     *
     * @return int|null the user id or null, if not authenticated
     */
    public function authenticate(string $login, string $password, ?string $factor): ?int;

    /**
     * Get a message for failed logins
     * - Should be just "username or password is not valid"
     *   if the login is not found or if the password is wrong or if the local authentication is not supported
     * - Can be an extended message if the user account is not active
     */
    public function failedMessage(string $login): string;

    /**
     * Check if a user account is active
     */
    public function isActive(int $user_id): bool;

    /**
     * Get the user agreement that is relevant for a user (simple HTML text)
     */
    public function getAgreement(int $user_id): string;

    /**
     * Check if the user has accepted the user agreement
     */
    public function hasAcceptedAgreement(int $user_id): bool;

    /**
     * Set the user agreement to 'accepted'
     */
    public function acceptAgreement(int $user_id): void;
}
