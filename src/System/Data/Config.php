<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

/**
 * Global configuration of the assessment-service
 * This can be changed via client api
 */
abstract class Config implements SystemEntity
{
    /**
     * URL of the writer web app
     * This can be set for development purposes
     * Otherwise the built-in app is used
     */
    abstract public function getWriterUrl(): ?string;
    abstract public function setWriterUrl(?string $writer_url): Config;

    /**
     * URL of the corrector web app
     * This can be set for development purposes
     * Otherwise the built-in app is used
     */
    abstract public function getCorrectorUrl(): ?string;
    abstract public function setCorrectorUrl(?string $corrector_url): Config;

    /**
     * Get the primary background color for buttons in the web app
     * e.g. '04427E'
     */
    abstract public function getPrimaryColor(): ?string;
    abstract public function setPrimaryColor(?string $primary_color): Config;

    /**
     * Get the primary text color for buttons in the web app
     * e.g. 'FFFFFF'
     */
    abstract public function getPrimaryTextColor(): ?string;
    abstract public function setPrimaryTextColor(?string $primary_text_color): Config;

    /**
     * Get how to respond to REST calls
     */
    abstract public function getSimulateOffline(): bool;
    abstract public function setSimulateOffline(bool $simulate_offline): Config;

    /**
     * Get the file path to the ghostscript executable
     */
    abstract public function getPathToGhostscript(): ?string;
    abstract public function setPathToGhostscript(?string $path_to_ghostscript): Config;

    abstract public function getHashAlgo(): string;
    abstract public function setHashAlgo(string $algo): Config;

}
