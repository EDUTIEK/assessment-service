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

namespace Edutiek\AssessmentService\System\BackgroundTask;

use Exception;

class Service implements ClientManager
{
    public function __construct(
        private readonly ClientManager $manager
    )
    {
    }

    public function run(string $component, array $component_args, string $title, string $job, ...$args): void
    {
        if (!is_a($job, Job::class, true)) {
            throw new Exception('Trying to run ' . $job . ', which does not implement: ' . Job::class);
        }
        $this->manager->run($component, $component_args, $title, $job, ...$args);
    }
}
