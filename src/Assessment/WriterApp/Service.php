<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WriterApp;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Apps\OpenHelper;
use Edutiek\AssessmentService\Assessment\Apps\RestHelper;
use Edutiek\AssessmentService\Assessment\Apps\RestService;
use Slim\App;

class Service implements OpenService, RestService
{
    public function __construct(
        private readonly int $ass_id,
        private readonly int $context_id,
        private readonly int $user_id,
        private readonly OpenHelper $open_helper,
        private readonly RestHelper $rest_helper,
        private readonly App $app,
        private readonly Repositories $repos,
    ) {
    }

    /**
     * Open the frontend
     */
    public function open(): never
    {
        exit;
    }

    /**
     * Handle a REST call
     */
    public function handle(): never
    {
        exit;
    }
}
