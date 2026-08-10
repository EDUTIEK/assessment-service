<?php

namespace Edutiek\AssessmentService\Views\Data;

use DateTimeImmutable;

/**
 * Summarized information about the use of the writer web app by a writer
 */
class ClientSummary
{
    public function __construct(
        private int $sessions,
        private ?DateTimeImmutable $first_access,
        private ?DateTimeImmutable $last_access,
        private ?float $battery = null,
        private ?bool $hidden = null,
    ) {
    }

    /**
     * Number of ILIAS sessions from which the web app was opened
     */
    public function getSessions(): int
    {
        return $this->sessions;
    }

    /**
     * Time of the first access to the web app (first opening)
     */
    public function getFirstAccess(): ?DateTimeImmutable
    {
        return $this->first_access;
    }

    /**
     * Time of the last recognized access from the web app (last sync call)
     */
    public function getLastAccess(): ?DateTimeImmutable
    {
        return $this->last_access;
    }

    /**
     * Battery status of the client sent with the last sync call
     */
    public function getBattery(): ?float
    {
        return $this->battery;
    }


    public function getHidden(): ?bool
    {
        return $this->hidden;
    }
}
