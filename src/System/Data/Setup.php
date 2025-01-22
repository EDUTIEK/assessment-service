<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

/**
 * Setup of the hosting system and the inclusion of the assessment-service
 * This can't be changed via client api
 */
abstract class Setup
{
    /**
     * Get a name identifying the hosting system
     * Used e.g. as "creator" in metadata of generated PDF files
     */
    abstract public function getSystemName(): string;

    /**
     * Get the base URL of the frontend web apps (writer and corrector)
     * The frontends are prebuild in the node_modules folder
     * They may be moved by the hosting system to a public directory
     * The getFrontendUrl() of
     */
    abstract public function getFrontendsBaseUrl(): string;

    /**
     * Get the URL of the backend for REST calls from the web apps
     * This must a URL of the hosting system that accepts GET and POST requests
     * The web apps will add a path to this URL but keep all existing query params
     */
    abstract public function getBackendUrl(): string;

    /**
     * Get the absolute path for temp files
     * It must be without a trailing slash
     * The PDF generation will store temporary images there
     */
    abstract public function getAbsoluteTempPath(): string;

    /**
     * Get a path for temp files which is relative to the current directory
     * It must be without a trailing slash
     * It must correspond to getRelativeTempPath
     * TCPDF requires this path for image sources
     */
    abstract public function getRelativeTempPath(): string;

    /**
     * Get the default path of the ghostscript executable
     * This is taken, if Config::getPathToGhostscript is not set
     */
    abstract function getDefaultPathToGhostscript(): ?string;
}
