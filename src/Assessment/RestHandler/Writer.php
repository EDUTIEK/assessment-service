<?php

namespace Edutiek\AssessmentService\Assessment\RestHandler;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Slim\App;

class Writer
{
    public function __construct(
        private readonly int $ass_id,
        private readonly int $context_id,
        private readonly int $user_id,
        private readonly App $app,
        private readonly RestHelper $helper,
        private readonly Repositories $repos,
    ) {
    }

    public function run()
    {


    }
}
