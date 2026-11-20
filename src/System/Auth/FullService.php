<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Auth;

use Edutiek\AssessmentService\System\Config\Frontend;
use Edutiek\AssessmentService\System\Data\Result;

interface FullService
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

    /**
     * Generate a new code and send an e-mail with that code
     * - set the code for all users that have this e-mail
     * - add the logins of all assigned users to this e-mail
     * - check validity time of old and new code
     * - if time difference is below 5 seconds, reject
     */
    public function requestPasswordChange(string $email, Frontend $frontend): bool;

    /**
     * Remove a password change code from users with an e-mail
     */
    public function removePasswordChange(string $email): void;

    /**
     * Check if the password change code is still valid
     * Keep the code until the password is changed
     */
    public function checkPasswordChangeCode(string $login, string $code): bool;

    /**
     * Change the password of a user
     */
    public function changePassword(string $login, string $password): Result;

}
