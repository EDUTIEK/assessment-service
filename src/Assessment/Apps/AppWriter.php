<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Apps;

use Edutiek\AssessmentService\System\Config\Frontend;

class AppWriter extends BaseApp implements RestService
{
    protected Frontend $frontend = Frontend::WRITER;

    public function handle(): never
    {
        $this->app->get('/writer/data', [$this,'getData']);
        $this->app->get('/writer/update', [$this,'getUpdate']);
        $this->app->get('/writer/file/{component}/{entity}/{id}', [$this,'getFile']);
        $this->app->put('/writer/changes', [$this, 'putChanges']);
        $this->app->put('/writer/final', [$this, 'putChanges']);
        $this->app->run();
        exit;
    }
}
