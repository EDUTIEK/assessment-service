<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Apps;

use Edutiek\AssessmentService\System\Config\Frontend;

class AppCorrector extends BaseApp implements RestService
{
    protected Frontend $frontend = Frontend::CORRECTOR;

    public function handle(): never
    {
        $this->app->get('/corrector/data', [$this,'getData']);
        $this->app->get('/corrector/file/{component}/{entity}/{id}', [$this,'getFile']);
        $this->app->run();
        exit;
    }
}
