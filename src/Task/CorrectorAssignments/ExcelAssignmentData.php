<?php

namespace Edutiek\AssessmentService\Task\CorrectorAssignments;

use Edutiek\AssessmentService\Assessment\Data\Corrector;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;
use Edutiek\AssessmentService\Task\CorrectorAssignments\FullService as CorrectorAssignmentService;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskInfo;
use Edutiek\AssessmentService\System\Language\Service as LanguageService;
use Edutiek\AssessmentService\System\Data\UserData;
use Edutiek\AssessmentService\System\User\ReadService as UserService;
use Edutiek\AssessmentService\Task\Data\Settings;
use Edutiek\AssessmentService\Assessment\Data\Location;
use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;
use Edutiek\AssessmentService\Assessment\Corrector\FullService as CorrectorService;
use Edutiek\AssessmentService\Assessment\Writer\FullService as WriterService;
use Edutiek\AssessmentService\Assessment\Location\ReadService as LocationService;
use Edutiek\AssessmentService\System\Spreadsheet\FullService as SpreadsheetService;

class ExcelAssignmentData
{
    private array $errors = [];
    /**
     * @var Settings[]
     */
    private array $tasks = [];
    /**
     * @var Corrector[]
     */
    private array $correctors = [];
    /**
     * @var UserData[]
     */
    private array $users_by_id = [];
    /**
     * @var UserData[]
     */
    private array $users_by_login = [];
    /**
     * @var CorrectorAssignment[]
     */
    private array $assignments = [];
    /**
     * @var Writer[]
     */
    private array $writers = [];

    /**
     * @var Location[]
     */
    private array $locations = [];
    private bool $multi_task;
    private int $needed_correctors;
    /**
     * @var Writer[]
     */
    private array $writers_by_user = [];
    /**
     * @var Corrector[]
     */
    private array $correctors_by_user = [];

    public function __construct(
        private CorrectionSettings $correction_settings,
        private OrgaSettings $orga_settings,
        array $tasks,
        private CorrectorService $corrector_service,
        private WriterService $writer_service,
        CorrectorAssignmentService $corrector_assignment_service,
        LocationService $location_service,
        private UserService $user_service,
        private readonly LanguageService $lng,
    ) {
        $this->multi_task = $this->orga_settings->getMultiTasks();
        $this->needed_correctors = $this->correction_settings->getRequiredCorrectors() + ($this->correction_settings->isStitchPossible() ? 1 : 0);

        foreach ($writer_service->all() as $writer) {
            $this->writers[$writer->getId()] = $writer;
            $this->writers_by_user[$writer->getUserId()] = $writer;
            $this->assignments[$writer->getId()] = [];
        }

        usort($tasks, fn (Settings $a, Settings $b) => $a->getPosition() <=> $b->getPosition());
        foreach ($tasks as $task) {
            $this->tasks[$task->getTaskId()] = $task;
        }

        foreach ($corrector_service->all() as $c) {
            $this->correctors[$c->getId()] = $c;
            $this->correctors_by_user[$c->getUserId()] = $c;
        }
        $user_ids = array_map(fn (Corrector $x) => $x->getUserId(), $this->correctors);
        $user_ids += array_map(fn (Writer $x) => $x->getUserId(), $this->writers);

        foreach ($user_service->getUsersByIds($user_ids) as $user) {
            $this->users_by_id[$user->getId()] = $user;
            $this->users_by_login[$user->getLogin()] = $user;
        }

        foreach ($corrector_assignment_service->all() as $assignment) {
            $this->assignments[$assignment->getWriterId()][$assignment->getPosition()->value] = $assignment;
        }

        foreach ($location_service->all() as $location) {
            $this->locations[$location->getId()] = $location;
        }
    }

    public function filterWriter(bool $authorized = true): void
    {
        $this->writers = array_filter($this->writers, fn (Writer $w) => $authorized ? $w->getWritingAuthorized() !== null : true);
    }

    /**
     * @return string[]
     */
    public function correctorHeader(): array
    {
        return [
            $this->lng->txt('login'),
            $this->lng->txt('firstname'),
            $this->lng->txt('lastname'),
            $this->lng->txt('email'),
        ];
    }

    /**
     * @return string[]
     */
    public function writerHeader(): array
    {
        $lng = $this->lng;
        $header = [
            $lng->txt('login'),
            $lng->txt('firstname'),
            $lng->txt('lastname'),
            $lng->txt('email'),
            $lng->txt('pseudonym'),
            $lng->txt('location'),
            $lng->txt('authorized'),
        ];
        if ($this->multi_task) {
            foreach ($this->tasks as $task) {
                $header[] = $task->getTitle();
            }
        } else {
            foreach (range(0, $this->needed_correctors - 1) as $pos) {
                $header[] = $lng->txt('corrector') . ' ' . ($pos + 1);
            }
        }

        return $header;
    }

    public function correctorBody(): array
    {
        $rows = [];

        foreach ($this->correctors as $corrector) {
            $user_data = $this->users_by_id[$corrector->getUserId()] ?? null;
            if ($user_data === null) {
                continue;
            }

            $rows[] = [
                $user_data->getLogin(),
                $user_data->getFirstname(),
                $user_data->getLastname(),
                $user_data->getEmail()
            ];
        }
        return $rows;
    }
    public function writerBody(): array
    {
        $rows = [];

        foreach ($this->writers as $w) {
            $user_data = $this->users_by_id[$w->getUserId()] ?? null;
            $ass = $this->assignments[$w->getId()] ?? [];

            if ($user_data === null) {
                continue;
            }
            $location = $this->locations[$w->getLocation()] ?? null;

            $row = [
                $user_data->getLogin(),
                $user_data->getFirstname(),
                $user_data->getLastname(),
                $user_data->getEmail(),
                $w->getPseudonym(),
                $location?->getTitle() ?? "",
                $w->getWritingAuthorized()?->format("d/m/Y H:i") ?? "",
            ];
            //Target formular for dropdown list.
            //$std_value = '=\'' . $this->lng->txt('corrector') . '\'!$A$2:$A$' . (count($this->correctors) + 1);;
            $std_value = '';

            if ($this->multi_task) {
                $ass_by_task = array_map(fn ($x) => $x, array_map(fn (CorrectorAssignment $x) => $x->getTaskId(), $ass), $ass);
                foreach ($this->tasks as $task) {
                    $value = $std_value;
                    if (isset($ass_by_task[$task->getTaskId()]) && isset($this->correctors[$ass_by_task[$task->getTaskId()]])) {
                        $c = $this->correctors[$ass_by_task[$task->getTaskId()]->getCorrectorId()];
                        $c_data = $this->users_by_id[$c?->getUserId()] ?? null;
                        $value = $c_data?->getLogin() ?? '';
                    }
                    $row[] = $value;
                }
            } else {
                foreach (range(0, $this->needed_correctors - 1) as $pos) {
                    $value = $std_value;

                    if (isset($ass[$pos]) && isset($this->correctors[$ass[$pos]->getCorrectorId()])) {
                        $c = $this->correctors[$ass[$pos]->getCorrectorId()];
                        $c_data = $this->users_by_id[$c?->getUserId()] ?? null;
                        $value = $c_data?->getLogin() ?? '';
                    }
                    $row[] = $value;
                }
            }

            $rows[] = $row;
        }
        return $rows;
    }

    public function importAssignments(array $data): array
    {
        $first = true;
        $writer_login_index = 0;
        $corrector_login_index = [];
        $error = false;
        $first_task_id = $this->tasks[array_key_first($this->tasks)]?->getTaskId() ?? 0;
        $writer_assignments = [];

        foreach ($data as $row_id => $row) {
            if ($first) {
                $writer_login_index = $this->findKey($row, fn ($x) => $x === $this->lng->txt('login') || $x === "Login"); // Login for backwards compatibility
                if ($this->multi_task) {
                    foreach ($this->tasks as $task) {
                        $corrector_login_index[$task->getTitle()] = $this->findKey($row, fn ($x) => $x === $task->getTitle());
                    }
                } else {
                    foreach (range(0, $this->needed_correctors - 1) as $pos) {
                        $corrector_login_index[$pos] = $this->findKey(
                            $row,
                            fn ($x) => $x === $this->lng->txt('corrector') . ' ' . ($pos + 1) ||
                                       $x === 'Corrector ' . ($pos + 1)// Corrector for backwards compatibility
                        );
                    }
                }

                $first = false;
                continue;
            }
            $writer_login = $row[$writer_login_index] ?? "";
            $writer_id = $this->getWriterByLogin($writer_login)?->getId();

            if ($writer_id === null) {
                $this->errors[] = ["writer_not_found", [$row_id+1, $writer_login]];
                continue;
            }

            $assignments = [];
            if ($this->multi_task) {
                foreach ($this->tasks as $task) {
                    $login = $row[$corrector_login_index[$task->getTitle()]] ?? "";
                    $corrector_id = $this->getCorrectorByLogin($login)?->getId();

                    if (!empty($login) && $corrector_id === null) {
                        $this->errors[] = ["corrector_not_found_task", [$row_id+1, $login, $task->getTitle()]];
                        continue;
                    } elseif (empty($login)) {
                        $corrector_id = Service::BLANK_CORRECTOR_ASSIGNMENT;
                    }
                    $assignments[] = [$corrector_id, 0, $task->getTaskId(), $row_id];
                }

            } else {
                foreach (range(0, $this->needed_correctors - 1) as $pos) {
                    $login = $row[$corrector_login_index[$pos]] ?? "";
                    $corrector_id = $this->getCorrectorByLogin($login)?->getId();

                    if (!empty($login) && $corrector_id === null) {
                        $this->errors[] = ["corrector_not_found_pos", [$row_id+1, $login, $pos + 1]];
                        continue;
                    } elseif (empty($login)) {
                        $corrector_id = Service::BLANK_CORRECTOR_ASSIGNMENT;
                    }

                    $assignments[] = [$corrector_id, $pos, $first_task_id, $row_id];
                }
            }

            $writer_assignments[$writer_id] = $assignments;
        }
        return $writer_assignments;
    }

    public function getErrors(): array
    {
        return array_map(fn ($e) => sprintf($this->lng->txt($e[0]), ...$e[1]), $this->errors);
    }

    public function isMultiTask(): bool
    {
        return $this->multi_task;
    }

    public function getNeededCorrectors(): int
    {
        return $this->needed_correctors;
    }

    private function getUserByLogin(string $login): ?UserData
    {
        $user_data =  $this->users_by_login[$login] ?? null;

        if ($user_data === null) {
            $user_id = $this->user_service->getUserIdByLogin($login);
            if ($user_id !== null) {
                $user_data = $this->user_service->getUser($user_id);
                $this->users_by_login[$login] = $user_data;
                $this->users_by_id[$user_id] = $user_data;
            }
        }
        return $user_data;
    }

    private function getCorrectorByLogin(string $login): ?Corrector
    {
        $user_data = $this->getUserByLogin($login);
        if ($user_data === null) {
            return null;
        }
        $corrector = $this->correctors_by_user[$user_data->getId()] ?? null;

        if ($corrector == null) {
            $corrector = $this->corrector_service->getByUserId($user_data->getId());
            if ($corrector !== null) {
                $this->correctors_by_user[$user_data->getId()] = $corrector;
                $this->correctors[$corrector->getId()] = $corrector;
            }
        }
        return $corrector;
    }

    private function getWriterByLogin(string $login): ?Writer
    {
        $user_data = $this->getUserByLogin($login);
        if ($user_data === null) {
            return null;
        }
        $writer = $this->writers_by_user[$user_data->getId()] ?? null;

        if ($writer == null) {
            $writer = $this->writer_service->getByUserId($user_data->getId());
            if ($writer !== null) {
                $this->writers_by_user[$user_data->getId()] = $writer;
                $this->writers[$writer->getId()] = $writer;
            }
        }
        return $writer;
    }

    /**
     * Only needed because array_find_key is not available blow PHP 8.4
     *
     * @param array    $array
     * @param callable $callback
     * @return mixed
     */
    private function findKey(array $array, callable $callback): mixed
    {
        foreach ($array as $key => $value) {
            if ($callback($value)) {
                return $key;
            }
        }
        return null;
    }

}
