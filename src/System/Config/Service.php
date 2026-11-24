<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Config;

use Edutiek\AssessmentService\System\Data\Config;
use Edutiek\AssessmentService\System\Data\ConfigRepo;
use Edutiek\AssessmentService\System\Data\Setup;
use Edutiek\AssessmentService\System\Data\SetupRepo;

readonly class Service implements ReadService, FullService
{
    public function __construct(
        private ConfigRepo $config_repo,
        private SetupRepo $setup_repo
    ) {
    }

    public function getConfig(): Config
    {
        return $this->config_repo->one();
    }

    public function saveConfig(Config $config): void
    {
        $this->config_repo->save($config);
    }

    public function getSetup(): Setup
    {
        return $this->setup_repo->one();
    }

    public function getFrontendUrl(Frontend $frontend, ?string $query = null): string
    {
        $url = match($frontend) {
            Frontend::WRITER => $this->getConfig()->getWriterUrl() ?? $this->buildFrontendUrl($frontend),
            Frontend::CORRECTOR => $this->getConfig()->getCorrectorUrl() ?? $this->buildFrontendUrl($frontend)
        };

        if ($query !== null) {
            $url .= '?' . $query;
        }

        return $url;
    }

    public function getPathToGhostscript(): ?string
    {
        return $this->getConfig()->getPathToGhostscript() ?? $this->getSetup()->getDefaultPathToGhostscript();
    }

    public function getPathToPdfTk(): ?string
    {
        return $this->getConfig()->getPathToPdftk() ?? '/usr/bin/pdftk';
    }

    /**
     * Build the URL of a frontend web app
     * Version 10: Add a query string with the revision to avoid an outdated cached app
     * Version 11: Web apps may be bookmarked and must have an unchanging URL
     */
    private function buildFrontendUrl(Frontend $frontend): string
    {
        return $this->getSetup()->getFrontendBaseUrl($frontend);

        //        $json = json_decode(file_get_contents(__DIR__ . '/../../../package-lock.json'), true);
        //        $resolved = $json['packages']['node_modules/' . $frontend->module()]['resolved'] ?? '';
        //        $revision = (string) parse_url($resolved, PHP_URL_FRAGMENT);
        //
        //        return $this->getSetup()->getFrontendBaseUrl($frontend)
        //            . '/index.html?' . substr($revision, 0, 7);
    }
}
