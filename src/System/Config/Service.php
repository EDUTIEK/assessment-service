<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Config;

use Edutiek\AssessmentService\System\Api\Dependencies;
use Edutiek\AssessmentService\System\Data\Config;
use Edutiek\AssessmentService\System\Data\ConfigRepo;
use Edutiek\AssessmentService\System\Data\Setup;

readonly class Service implements ReadService, FullService
{
    public function __construct(
        private ConfigRepo $config_repo
    ) {
    }

    public function getConfig(): Config
    {
        return $this->config_repo->getConfig();
    }

    public function saveConfig(Config $config): void
    {
        $this->config_repo->saveConfig($config);
    }

    public function getSetup(): Setup
    {
        return $this->config_repo->getSetup();
    }

    public function getFrontendUrl(FrontendModule $module): string
    {
        switch ($module) {
            case FrontendModule::WRITER:
                return $this->getConfig()->getWriterUrl() ?? $this->buildFrontendUrl('assessment-writer');
            case FrontendModule::CORRECTOR:
                return $this->getConfig()->getCorrectorUrl() ?? $this->buildFrontendUrl('assessment-corrector');
        }
        return '';
    }

    /**
     * Build the URL of a frontend web app
     * Add a query string with the revision to avoid an outdated cached app
     * @param string $module name and path of the web app in node_modules
     */
    private function buildFrontendUrl(string $module): string
    {
        $json = json_decode(file_get_contents(__DIR__ . '/../../../package-lock.json'), true);
        $resolved = $json['packages']['node_modules/' . $module]['resolved'] ?? '';
        $revision = (string) parse_url($resolved, PHP_URL_FRAGMENT);

        return $this->getSetup()->getFrontendsBaseUrl()
            . '/' . $module . '/' . '/dist/index.html?' . substr($revision, 0, 7);
    }
}
