<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

use DateTimeZone;

/**
 * Data of a system user that is used by the assessment-service
 * This is only read. The service does not create users or change their data
 */
abstract class UserData implements SystemEntity
{
    /**
     * Unique ID with which the user is stored and can be found
     */
    abstract public function getId(): int;

    /**
     * Login name of the user account, e.g. "rsmith"
     */
    abstract public function getLogin(): string;

    /**
     * Title of the user, e.g. "Dr."
     */
    abstract public function getTitle(): string;

    /**
     * First name of the user, e.g. "Robert"
     */
    abstract public function getFirstname(): string;

    /**
     * Last name of the user, e.G. "Smith"
     */
    abstract public function getLastname(): string;

    /**
     * Get the language of the user as ISO 639-1 two-letter code, e.g. 'en'
     */
    abstract public function getLanguage(): string;

    /**
     * Get the time zone of the user for display of dates
     */
    abstract public function getTimezone(): DateTimeZone;

    /**
     * Full name for documentation or salutation, e.g. "Dr. Robert Smith"
     */
    public function getFullname(bool $with_login): string
    {
        $name = $this->getTitle();

        if ($this->getFirstname() !== '') {
            $name = ($name !== '' ? $name . ' ' : '') . $this->getFirstname();
        }
        if ($this->getLastname() !== '') {
            $name = ($name !== '' ? $name . ' ' : '') . $this->getLastname();
        }
        if ($with_login && $this->getLogin() !== '') {
            $name = ($name !== '' ? $name . ' ' : '') . '[' . $this->getLogin() . ']';
        }

        return $name;
    }

    /**
     * Name for using in lists, e.g. "Smith, Robert [rsmith]"
     */
    public function getListname(bool $with_login): string
    {
        $name = $this->getLastname();

        if ($this->getTitle() !== '') {
            $name = $this->getTitle() . ' ' . $name;
        }
        if ($this->getFirstname() !== '') {
            $name = ($name !== '' ? $name . ', ' : '') . $this->getFirstname();
        }
        if ($with_login && $this->getLogin() !== '') {
            $name = ($name !== '' ? $name . ' ' : '') . '[' . $this->getLogin() . ']';
        }

        return $name;
    }

    /**
     * 2-Letter string of either the initials or first letters of the login
     */
    public function getInitials(): string
    {
        if ($this->getFirstname() !== '' && $this->getLastname() !== '') {
            return ucfirst(substr($this->getFirstname(), 0, 1))
                . ucfirst(substr($this->getLastname(), 0, 1));
        } else {
            return strtoupper(substr($this->getLogin(), 0, 2));
        }
    }
}
