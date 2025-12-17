<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

abstract class UserDisplay implements SystemEntity
{
    /**
     * Unique ID with which the user is stored and can be found
     */
    abstract public function getId(): int;

    /**
     * URL of a user image that can be used as src attribute of an img element
     */
    abstract public function getImageUrl(): ?string;


    /**
     * Url for showing a user profile in the same window with a back link
     */
    abstract public function getProfileUrl(string $return_url): ?string;
}
