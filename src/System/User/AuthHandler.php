<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\User;

/**
 * Authentication functions needed from the hosting system
 */
interface AuthHandler
{
    /**
     * Check an entered password for a user
     */
    public function checkPassword(int $user_id, string $password): bool;

    /**
     * Handle a failed login attempt
     * This may deactivate a user
     */
    public function handleFailedLogin(int $user_id): void;

    /**
     * Check if a user account is active
     */
    public function isActive(int $user_id): bool;

    /**
     * Check if an authentication with password is enabled
     */
    public function isPasswordEnabled(int $user_id): bool;

    /**
     * Get the text of the user agreement that is required for the user
     */
    public function getAgreement($user_id): string;

    /**
     * Check if the user has accepted the agreement
     */
    public function hasAcceptedAgreement($user_id): bool;

    /**
     * Set the acceptance of the user agreement for a user
     */
    public function acceptAgreement($user_id): void;
}
