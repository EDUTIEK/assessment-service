<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\PdfCreation;

use Edutiek\AssessmentService\Assessment\Api\ComponentApiFactory;
use Edutiek\AssessmentService\Assessment\Data\PdfConfig;
use Edutiek\AssessmentService\Assessment\Data\PdfFormat;
use Edutiek\AssessmentService\Assessment\Data\PdfSettings;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Properties\ReadService as PropetiesReadService;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterService;
use Edutiek\AssessmentService\System\Config\ReadService as ConfigService;
use Edutiek\AssessmentService\System\File\Storage as FileStorage;
use Edutiek\AssessmentService\System\PdfCreator\Options;
use Edutiek\AssessmentService\System\PdfProcessing\FullService as PdfProcessingService;
use Edutiek\AssessmentService\System\User\ReadService as UserService;
use Edutiek\AssessmentService\Task\Manager\ReadService as TasksReadService;
use ZipArchive;

class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private int $context_id,
        private int $user_id,
        private ComponentApiFactory $apis,
        private WriterService $writers,
        private Repositories $repos,
        private PdfSettings $pdf_settings,
        private PdfProcessingService $processor,
        private ConfigService $config,
        private FileStorage $storage,
        private UserService $users,
        private TasksReadService $tasks,
        private PropetiesReadService $properties,
    ) {
    }

    public function getSortedParts(PdfPurpose $purpose): array
    {
        $configs = [];
        $max = 0;
        foreach ($this->repos->pdfConfig()->allByAssIdAndPurpose($this->ass_id, $purpose->value) as $config) {
            $configs[$config->getComponent()][$config->getKey()] = $config;
            $max = max($max, $config->getPosition());
        }

        $parts = [];
        foreach ($this->apis->components($this->ass_id, $this->user_id) as $component) {
            foreach ($this->getProvider($component, $purpose, false, false)?->getAvailableParts() ?? [] as $part) {
                $config = $configs[$part->getComponent()][$part->getKey()] ?? null;
                /** @var PdfConfig $config */
                if ($config !== null) {
                    $part->setIsActive($config->getIsActive());
                    $part->setPosition($config->getPosition());
                } else {
                    $part->setIsActive(true);
                    $part->setPosition($max++);
                }
                $parts[sprintf('part_%04d_%s_%s', $part->getPosition(), $part->getComponent(), $part->getKey())] = $part;
            }
        }
        sort($parts);
        return array_values($parts);
    }

    /**
     * Save the activation and sorting
     * @param PdfConfigPart[] $parts
     */
    public function saveSortedParts(PdfPurpose $purpose, array $parts): void
    {
        $repo = $this->repos->pdfConfig();
        $this->repos->pdfConfig()->deleteByAssIdAndPurpose($this->ass_id, $purpose->value);

        foreach ($parts as $part) {
            $repo->save($repo->new()
                ->setAssId($this->ass_id)
                ->setPurpose($purpose->value)
                ->setComponent($part->getComponent())
                ->setKey($part->getKey())
                ->setIsActive($part->getIsActive())
                ->setPosition($part->getPosition()));
        }
    }

    public function createWritingPdf(int $task_id, int $writer_id, bool $anonymous = false): string
    {
        return $this->createPdfFile(
            PdfPurpose::WRITING,
            $task_id,
            $writer_id,
            $anonymous,
            true
        );
    }

    public function createWritingZip(array $writings, bool $anonymous = false): string
    {
        return $this->createZipFile(
            PdfPurpose::WRITING,
            $writings,
            $anonymous,
            true
        );
    }

    public function createCorrectionPdf(int $task_id, int $writer_id, bool $anonymous_writer, bool $anonymous_corrector): string
    {
        return $this->createPdfFile(
            PdfPurpose::CORRECTION,
            $task_id,
            $writer_id,
            $anonymous_writer,
            $anonymous_corrector
        );
    }

    public function createCorrectionZip(array $writings, bool $anonymous_writer, bool $anonymous_corrector): string
    {
        return $this->createZipFile(
            PdfPurpose::CORRECTION,
            $writings,
            $anonymous_writer,
            $anonymous_corrector
        );
    }


    public function createCorrectionReport(int $ass_id): string
    {
        // TODO: Implement createCorrectionReport() method.
        return '';
    }

    private function createPdfFile(PdfPurpose $purpose, int $task_id, int $writer_id, bool $anonymous_writer, bool $anonymous_corrector): string
    {
        $options = (new Options())
            ->withTitle($this->buildTitle($task_id, $writer_id, $anonymous_writer));

        $pdf_ids = [];
        foreach ($this->getSortedParts($purpose) as $part) {
            if ($part->getIsActive()) {
                $provider = $this->getProvider($part->getComponent(), $purpose);
                $id = $provider->renderPart(
                    $part->getKey(),
                    $task_id,
                    $writer_id,
                    $anonymous_writer,
                    $anonymous_corrector,
                    $options,
                );
                if ($id !== null) {
                    $pdf_ids[] = $id;
                    $options = $options->withStartPageNumber(
                        $options->getStartPageNumber() + $this->processor->count($id)
                    );
                }
            }
        }

        if (count($pdf_ids) == 1) {
            $id = reset($pdf_ids);
            return $id;
        } else {
            $id = $this->processor->join($pdf_ids);
        }

        $this->processor->cleanupExcept([$id]);
        return $id;
    }

    private function buildTitle(int $task_id, int $writer_id, bool $anonymous_writer)
    {
        $writer = $this->writers->oneByWriterId($writer_id);
        $properties = $this->properties->get();

        if ($anonymous_writer) {
            $writer_name = $writer->getPseudonym();
        } else {
            $user = $this->users->getUser($writer?->getUserId() ?? 0);
            $writer_name = $user->getFullname(false);
        }

        switch ($this->pdf_settings->getFormat()) {
            case PdfFormat::BY:
                return $writer_name . ' | ' . $properties->getDescription();

            case PdfFormat::NRW:
            default:
                $title = $writer_name . ' | ' . $properties->getTitle();
                if ($this->tasks->count() > 1) {
                    $task = $this->tasks->one($task_id);
                    $title .= ' - ' . $task->getTitle();
                }
                return $title;
        }
    }

    private function createZipFile(PdfPurpose $purpose, array $writings, bool $anonymous_writer, bool $anonymous_corrector): string
    {
        $tasks = [];
        foreach ($this->tasks->all() as $task) {
            $tasks[$task->getId()] = $task;
        }
        $multi_tasks = count($tasks) > 1;

        $ids = [];
        foreach ($writings as $writing) {
            $ids[$writing->getWriterId()][$writing->getTaskId()] = $writing->getTaskId();
        }

        $zipfile = $this->config->getSetup()->getAbsoluteTempPath()
            . uniqid('', true) . '.zip';
        $zip = new ZipArchive();
        $zip->open($zipfile, ZipArchive::CREATE);

        $temp_files = [];
        foreach ($ids as $writer_id => $task_ids) {
            $writer = $this->writers->oneByWriterId($writer_id);
            $user = $this->users->getUser($writer?->getUserId());
            $name = $this->storage->asciiFilename($user->getListname(true));
            if ($multi_tasks) {
                $zip->addEmptyDir($name);
            }

            foreach ($task_ids as $task_id) {
                $pdf_id = $this->createPdfFile($purpose, $task_id, $writer_id, $anonymous_writer, $anonymous_corrector);
                if ($multi_tasks) {
                    $task = $tasks[$task_id] ?? null;
                    $entry = $name . '/' . $this->storage->asciiFilename($task?->getTitle() ?? 'task') . '.pdf';
                } else {
                    $entry = $name . '.pdf';
                }
                $zip->addFile($this->storage->getReadablePath($pdf_id), $entry);
                $temp_files[] = $pdf_id;
            }
        }
        $zip->close();
        foreach ($temp_files as $id) {
            $this->storage->deleteFile($id);
        }

        $fp = fopen($zipfile, 'r');
        $info = $this->storage->saveFile($fp, $this->storage->newInfo()
            ->setMimeType('application/zip'));

        unlink($zipfile);
        return $info->getId();
    }

    private function getProvider(string $component, PdfPurpose $purpose): ?PdfPartProvider
    {
        switch ($purpose) {
            case PdfPurpose::WRITING:
                return $this->apis->api($component)?->writingPartProvider($this->ass_id, $this->context_id, $this->user_id);

            case PdfPurpose::CORRECTION:
                return $this->apis->api($component)?->correctionPartProvider($this->ass_id, $this->context_id, $this->user_id);
        }
        return null;
    }

}
