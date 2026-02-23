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

interface SystemManager
{
    /**
     * Create a background task in the client system.
     * This is called by a service component to queue a background task for running
     *
     * To run the task, the system will have to:
     * - get the component with the component args
     * - get the component's backgroundTasks() service with the service args
     * - call the service's run() function with the job classname and job args
     *
     * @param string $title title of the job for interactive display
     * @param string $component name of the component, e.g. 'assessment', 'essayTask'
     * @param class-string<ComponentJob> $job name of the class that executes the job
     * @param array $component_args arguments needed to initialize the component (must be scalar)
     * @param array $service_args arguments needed to initialize the service (must be scalar)
 * @param array $job_args arguments needed to initialize the job (must be scalar)
 */
    public function create(
        string $title,
        string $component,
        string $job,
        array $component_args,
        array $service_args,
        array $job_args,
    ): void;
}
