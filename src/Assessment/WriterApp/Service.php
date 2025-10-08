<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WriterApp;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Apps\OpenHelper;
use Edutiek\AssessmentService\Assessment\Apps\RestHelper;
use Edutiek\AssessmentService\Assessment\Apps\RestService;
use Edutiek\AssessmentService\System\Config\FrontendModule;
use Edutiek\AssessmentService\System\Config\ReadService as ConfigService;
use Slim\App;

class Service implements OpenService, RestService
{
    public function __construct(
        private readonly int $ass_id,
        private readonly int $context_id,
        private readonly int $user_id,
        private readonly ConfigService $config,
        private readonly OpenHelper $open_helper,
        private readonly RestHelper $rest_helper,
        private readonly App $app,
        private readonly Repositories $repos,
    ) {
    }

    public function open(string $return_url): never
    {
        $this->open_helper->setCommonFrontendParams($return_url);

        // add the hash of the current essay content
        // this will be used to check if the writer content is outdated
        // todo: either use hashes of all essays or omit
        //$this->open_helper->setFrontendParam('Hash', (string) $essay->getWrittenHash());

        $this->open_helper->openFrontend($this->config->getFrontendUrl(FrontendModule::WRITER));
    }

    /**
     * Handle a REST call
     */
    public function handle(): never
    {
        exit;
    }
}
