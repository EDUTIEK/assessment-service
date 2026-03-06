<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Export;

use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\ResultExportFormat;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\Data\Corrector;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingProvider;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskManager;
use Edutiek\AssessmentService\Assessment\Writer\FullService as WriterService;
use Edutiek\AssessmentService\Assessment\Corrector\FullService as CorrectorService;
use Edutiek\AssessmentService\Assessment\AssessmentGrading\FullService as GradingService;
use Edutiek\AssessmentService\Assessment\Format\FullService as FormatService;
use Edutiek\AssessmentService\System\Spreadsheet\FullService as Spreadsheets;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\System\Spreadsheet\ExportType;
use Edutiek\AssessmentService\System\User\ReadService as UserService;
use Edutiek\AssessmentService\System\Format\FullService as SysFormatService;

readonly class ResultsExport
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private Repositories $repos,
        private CorrectionSettings $correction_settings,
        private WriterService $writers,
        private CorrectorService $correctors,
        private GradingService $grades,
        private TaskManager $tasks,
        private GradingProvider $grading,
        private Language $lang,
        private FormatService $format,
        private Spreadsheets $spreadsheets,
        private UserService $users,
        private SysFormatService $sys_format,
    ) {
    }


    public function create(): string
    {
        $settings = $this->repos->exportSettings()->one($this->ass_id) ?? $this->repos->exportSettings()->new();
        switch ($settings->getResultExportFormat()) {
            case ResultExportFormat::EDUTIEK:
                return $this->createEdutiekExport();
            case ResultExportFormat::EXAMIS:
                return $this->createExamisExport();
            case ResultExportFormat::JUSTA:
                return $this->createJustaExport();
        }
        return '';
    }

    private function createEdutiekExport(): string
    {
        $tasks = $this->tasks->all();

        $header = [
            'login' => $this->lang->txt('export_login'),
            'firstname' => $this->lang->txt('export_firstname'),
            'lastname' => $this->lang->txt('export_lastname'),
            'matriculation' => $this->lang->txt('export_matriculation'),
            'writing_status' => $this->lang->txt('export_writing_status'),
            'writing_authorized' => $this->lang->txt('export_writing_authorized'),
            'correction_status' => $this->lang->txt('export_correction_status'),
            'correction_finalized' => $this->lang->txt('export_correction_finalized'),
            'points' => $this->lang->txt('export_points'),
            'grade' => $this->lang->txt('export_grade'),
            'grade_code' => $this->lang->txt('export_grade_code'),
            'passed' => $this->lang->txt('export_passed'),
        ];

        if ($this->correction_settings->hasMultipleCorrectors()) {
            foreach (GradingPosition::all() as $pos) {
                $corrector_label = $this->lang->txt($pos->languageVariable());
                $header['corrector_' . $pos->value . '_login'] = $corrector_label . ' ' . $this->lang->txt('export_login');
                $header['corrector_' . $pos->value . '_name'] = $corrector_label . ' ' . $this->lang->txt('export_name');
                $header['corrector_' . $pos->value . '_points'] = $corrector_label . ' ' . $this->lang->txt('export_points');
            }
        } else {
            foreach ($tasks as $task_pos => $task) {
                $pos = (int) $task->getPosition();
                $corrector_label = count($tasks) == 1
                    ? $this->lang->txt('export_corrector')
                    : $this->lang->txt('export_corrector_task_x', ['pos' => $task_pos + 1]);
                $header['corrector_' . $task_pos . '_login'] = $corrector_label . ' ' . $this->lang->txt('export_login');
                $header['corrector_' . $task_pos . '_name'] = $corrector_label . ' ' . $this->lang->txt('export_name');
                $header['corrector_' . $task_pos . '_points'] = $corrector_label . ' ' . $this->lang->txt('export_points');
            }
        }

        $writers = $this->writers->all();
        $correctors = [];
        foreach ($this->correctors->all() as $corrector) {
            $correctors[$corrector->getId()] = $corrector;
        }

        $user_ids = array_unique(array_merge(
            array_map(fn(Writer $writer) => $writer->getUserId(), $writers),
            array_map(fn(Corrector $corrector) => $corrector->getUserId(), $correctors)
        ));

        $users = $this->users->getUsersByIds($user_ids);

        $rows = [];
        foreach ($writers as $writer) {
            $user = $users[$writer->getUserId()] ?? null;
            $level = $this->grades->getGradLevelForPoints($writer->getFinalPoints());

            $row = [
                'login' => $user?->getLogin(),
                'firstname' => $user?->getFirstname(),
                'lastname' => $user?->getLastname(),
                'matriculation' => $user?->getMatriculation(),
                'writing_status' => $this->format->writingStatus($writer),
                'writing_authorized' => $this->sys_format->logDate($writer->getWritingAuthorized()),
                'correction_status' => $this->lang->txt($writer->getCorrectionStatus()->languageVariable()),
                'correction_finalized' => $this->sys_format->logDate($writer->getCorrectionFinalized()),
                'points' => $writer->isCorrectionFinalized() ? $writer->getFinalPoints() : null,
                'grade' => $level?->getGrade(),
                'grade_code' => $level?->getCode(),
                'passed' => $level?->getPassed() ? $this->lang->txt('export_yes') : $this->lang->txt('export_no')
            ];

            if ($this->correction_settings->hasMultipleCorrectors()) {
                foreach ($this->grading->gradingsForTaskAndWriter(reset($tasks)->getId(), $writer->getId()) as $pos => $grading) {
                    if ($grading?->isAuthorized()) {
                        $pos = $grading->getPosition();
                        $corrector = $correctors[$grading->getCorrectorId()] ?? null;
                        $user = $users[$corrector?->getUserId() ?? 0] ?? null;
                        $row['corrector_' . $pos->value . '_login'] = $user?->getLogin();
                        $row['corrector_' . $pos->value . '_name'] = $user?->getListname(false);
                        $row['corrector_' . $pos->value . '_points'] = $grading->getPoints();
                    }
                }
            } else {
                foreach ($tasks as $task_pos => $task) {
                    foreach ($this->grading->gradingsForTaskAndWriter($task->getId(), $writer->getId()) as $grading) {
                        if ($grading?->isAuthorized()) {
                            $corrector = $correctors[$grading->getCorrectorId()] ?? null;
                            $user = $users[$corrector?->getUserId() ?? 0] ?? null;
                            $row['corrector_' . $task_pos . '_login'] = $user?->getLogin();
                            $row['corrector_' . $task_pos . '_name'] = $user?->getListname(false);
                            $row['corrector_' . $task_pos . '_points'] = $grading->getPoints();
                        }
                        break;
                    }
                }
            }

            $rows[] = $row;
        }

        return $this->spreadsheets->dataToFile($header, $rows, ExportType::CSV, 'Ergebnisse');
    }

    private function createJustaExport()
    {
        return $this->createEdutiekExport();
    }

    private function createExamisExport()
    {
        return $this->createEdutiekExport();
    }
}
