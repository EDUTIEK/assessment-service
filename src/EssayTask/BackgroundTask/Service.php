<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\BackgroundTask;

use Edutiek\AssessmentService\System\BackgroundTask\Manager;
use Edutiek\AssessmentService\System\BackgroundTask\ClientManager;

class Service implements Manager
{
    public function __construct(
        private readonly int $ass_id,
        private readonly int $user_id,
        private readonly ClientManager $manager,
    ) {
    }

    public function run(string $title, string $job, ...$args): void
    {
        $this->manager->run('essayTask', [$this->ass_id, $this->user_id], $title, $job, ...$args);
    }
}
