<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

/**
 * Global configuration of the assessment-service
 * This can be changed via client api
 */
abstract class Config implements SystemEntity
{
    public const DEFAULT_PRIMARY_COLOR = '04427E';
    public const DEFAULT_PRIMARY_TEXT_COLOR = 'FFFFFF';
    public const DEFAULT_CORRECTOR1_COLOR = '83c9d6';
    public const DEFAULT_CORRECTOR2_COLOR = '91ce82';
    public const DEFAULT_CORRECTOR3_COLOR = 'eec751';
    public const DEFAULT_HASH_ALGO = 'sha256';

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
     * Get the comor for markings of corrector 1
     * e.g. '00F0FF'
     */
    abstract public function getCorrector1Color(): ?string;
    abstract public function setCorrector1Color(?string $corrector1_color): self;

    /**
     * Get the comor for markings of corrector 2
     * e.g. '00F0FF'
     */
    abstract public function getCorrector2Color(): ?string;
    abstract public function setCorrector2Color(?string $corrector2_color): self;

    /**
     * Get the comor for markings of corrector 3
     * e.g. '00F0FF'
     */
    abstract public function getCorrector3Color(): ?string;
    abstract public function setCorrector3Color(?string $corrector3_color): self;

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

    /**
     * Get the file path to the pdftk executable
     */
    abstract public function getPathToPdftk(): ?string;
    abstract public function setPathToPdftk(?string $path_to_pdftk): self;

    /**
     * Get the alorithm used for caching
     */
    abstract public function getHashAlgo(): string;
    abstract public function setHashAlgo(string $algo): Config;

    /**
     * Get the available options for hash algorithms
     */
    public function getHashAlgoOptions(): array
    {
        return array_combine(hash_algos(), hash_algos());
    }
}
