<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\AppBridges;

use Edutiek\AssessmentService\Assessment\Apps\AppCorrectorBridge;
use Edutiek\AssessmentService\Assessment\Apps\ChangeRequest;
use Edutiek\AssessmentService\Assessment\Data\Corrector;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Export\FullService as ExportService;
use Edutiek\AssessmentService\Assessment\PdfCreation\FullService as PdfService;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskChecks as ChecksService;
use Edutiek\AssessmentService\System\Config\ReadService as ConfigService;
use Edutiek\AssessmentService\System\Data\Config;
use Edutiek\AssessmentService\System\Entity\FullService as EntityService;
use Psr\Http\Message\UploadedFileInterface;
use Edutiek\AssessmentService\System\Data\FileInfo;
use Edutiek\AssessmentService\Assessment\Data\WritingTask;
use Edutiek\AssessmentService\System\File\Disposition;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\Assessment\Data\ExportType;

class CorrectorBridge implements AppCorrectorBridge
{
    private ?Corrector $corrector;

    private $tasks = [];
    private $resources = [];
    private bool $is_admin;

    public function __construct(
        private readonly int $ass_id,
        private readonly int $user_id,
        private readonly PdfService $pdfs,
        private readonly ChecksService $checks,
        private readonly ConfigService $config,
        private readonly EntityService $entity,
        private readonly Storage $storage,
        private readonly Repositories $repos,
    ) {
        $this->corrector = $this->repos->corrector()->oneByUserIdAndAssId($this->user_id, $this->ass_id);
    }

    public function setAdmin(bool $is_admin): static
    {
        $this->is_admin = $is_admin;
        return $this;
    }

    public function getData(bool $for_update): array
    {
        $config = $this->config->getConfig();

        $data['Config'] = $this->entity->arrayToPrimitives([
            'primary_color' => $config->getPrimaryColor() ?? Config::DEFAULT_PRIMARY_COLOR,
            'primary_text_color' => $config->getPrimaryTextColor() ?? Config::DEFAULT_PRIMARY_TEXT_COLOR,
            'corrector1_color' => $config->getCorrector1Color() ?? Config::DEFAULT_CORRECTOR1_COLOR,
            'corrector2_color' => $config->getCorrector2Color() ?? Config::DEFAULT_CORRECTOR2_COLOR,
            'corrector3_color' => $config->getCorrector3Color() ?? Config::DEFAULT_CORRECTOR3_COLOR,
        ]);

        $data['GradeLevels'] = [];
        foreach ($this->repos->gradeLevel()->allByAssId($this->ass_id) as $level) {
            $data['GradeLevels'][] = $this->entity->arrayToPrimitives([
                'id' => $level->getId(),
                'min_points' => $level->getMinPoints(),
                'title' => $level->getGrade(),
                'statement' => $level->getStatement(),
            ]);
        }

        $settings = $this->repos->correctionSettings()->one($this->ass_id);
        $data['Settings'] = $this->entity->arrayToPrimitives([
            'title' => $this->repos->properties()->one($this->ass_id)->getTitle(),
            'multiple_correctors' => $settings->hasMultipleCorrectors(),
            'mutual_visibility' => $settings->getMutualVisibility(),
            'procedure_when_distance' => $settings->getProcedureWhenDistance(),
            'procedure' => $settings->getProcedure(),
            'max_no_procedure_distance' => $settings->getMaxNoProcedureDistance(),
            'revision_between' => $settings->getRevisionBetween(),
            'stitch_when_distance' => $settings->getStitchWhenDistance(),
            'max_no_stitch_distance' => $settings->getMaxNoStitchDistance(),
            'max_points' => $settings->getMaxPoints(),
            'no_manual_decimals' => $settings->getNoManualDecimals(),
            'download_writing' => $this->is_admin || $settings->getDownloadWriting(),
            'download_correction' => $this->is_admin || $settings->getDownloadCorrection(),
            'fixed_items_order' => $settings->getStartpageFixed()
        ]);

        return $data;
    }

    public function getFileInfo(string $entity, int $entity_id, array $data = []): ?FileInfo
    {
        switch ($entity) {
            case 'writings':
                $writings = [];
                foreach ($data as $item) {
                    if (!isset($item['writer_id']) || !isset($item['task_id'])) {
                        continue;
                    }
                    $writing = new WritingTask(
                        (int) $item['writer_id'],
                        (int) $item['task_id']
                    );
                    if ($this->checks->hasTask($writing->getTaskId())
                        && $this->checks->hasWriter($writing->getWriterId())
                        && $this->is_admin || $this->checks->isAssigned(
                            $writing->getWriterId(),
                            $this->corrector?->getId() ?? 0,
                            $writing->getTaskId()
                        )
                    ) {
                        $writings[] = $writing;
                    }
                }

                if (count($writings) == 1) {
                    /** @var WritingTask $writing */
                    $writing = reset($writings);
                    $file_id = $this->pdfs->createWritingPdf($writing->getTaskId(), $writing->getWriterId(), true);
                    return $this->storage->newInfo()
                        ->setId($file_id)
                        ->setMimeType('application/pdf')
                        ->setDisposable(true);

                } elseif (count($writings) > 0) {
                    $file_id = $this->pdfs->createWritingZip($writings, true);
                    return $this->storage->newInfo()
                        ->setId($file_id)
                        ->setMimeType('application/zip')
                        ->setDisposable(true);
                }
        }
        // no files handled in assessment component
        return null;
    }

    public function applyChanges(string $type, array $changes): array
    {
        if ($this->corrector !== null) {
            switch ($type) {
                default:
                    return array_map(fn(ChangeRequest $change) => $change->toResponse(false, 'wrong type'), $changes);
            }
        }
        return array_map(fn(ChangeRequest $change) => $change->toResponse(false, 'corrector not found'), $changes);
    }

    public function getItem(int $task_id, int $writer_id): ?array
    {
        // no items handled in assessment component
        return null;
    }

    public function processUploadedFile(UploadedFileInterface $file, string $entity, ?int $task_id, ?int $writer_id): ?array
    {
        return null;
    }
}
