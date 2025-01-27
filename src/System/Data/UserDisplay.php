<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

abstract readonly class UserDisplay implements SystemEntity
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
     * URL of an embedded user profile page that can be used as src attribute of an iframe element
     */
    abstract public function getEmbeddedProfileUrl(): ?string;

    /**
     * Url for showing a user profile in the same window with a back link
     */
    abstract public function getLinkedProfileUrl(string $return_url): ?string;
}
