<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

use DateTimeZone;
use Edutiek\AssessmentService\System\Config\CronJobId;
use Edutiek\AssessmentService\System\Config\Frontend;

/**
 * Setup of the hosting system and the inclusion of the assessment-service
 * This can't be changed via client api
 */
abstract readonly class Setup
{
    /**
     * Get a name identifying the hosting system
     * Used e.g. as "creator" in metadata of generated PDF files
     */
    abstract public function getSystemName(): string;

    /**
     * Get the base URL of one frontend web app (writer or corrector)
     * The URL must not have a trailing slash
     * The hosting system decides where the built app is published
     */
    abstract public function getFrontendBaseUrl(Frontend $frontend): string;

    /**
     * Get the URL of the backend for REST calls from the web apps
     * This must a URL of the hosting system that accepts GET and POST requests
     * The web apps will add a path to this URL but keep all existing query params
     */
    abstract public function getBackendUrl(): string;

    /**
     * Get the absolute path for temp files
     * It must be without a trailing slash
     * Used by ZIPArchive, PHPSpreadsheet, Ghostscript, FileDelivery
     */
    abstract public function getAbsoluteTempPath(): string;

    /**
     * Get the absolute path for artifacts written by the service
     * It must be without a trailing slash
     * Used by Dompdf for fonts
     */
    abstract public function getAbsoluteArtifactsPath(): string;

    /**
     * Get the default path of the ghostscript executable
     * This is taken, if Config::getPathToGhostscript is not set
     */
    abstract public function getDefaultPathToGhostscript(): ?string;

    /**
     * Get the default language for documentations as ISO 639-1 two-letter code, e.g. 'en'
     */
    abstract public function getDefaultLanguage(): string;

    /**
     * Get the default time zone for documentations
     */
    abstract public function getDefaultTimezone(): DateTimeZone;

    /**
     * Get if a cron job is active
     */
    abstract public function isCronJobActive(CronJobId $job_id): bool;
}
