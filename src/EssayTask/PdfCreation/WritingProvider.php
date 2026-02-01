<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\PdfCreation;

use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\EssayTask\HtmlProcessing\FullService as HtmlProcessing;
use Edutiek\AssessmentService\System\PdfCreator\Options;
use Edutiek\AssessmentService\System\PdfProcessing\FullService as PdfProcessing;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfConfigPart;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfPartProvider;
use Edutiek\AssessmentService\Assessment\CorrectionSettings\ReadService as CorrectionSettingsReadService;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterReadService;
use Edutiek\AssessmentService\Assessment\Properties\ReadService as PropetiesReadService;
use Edutiek\AssessmentService\Task\Manager\ReadService as TaskManagerReadService;
use Edutiek\AssessmentService\System\User\ReadService as UserReadService;

readonly class WritingProvider implements PdfPartProvider
{
    public const PART_ESSAY = 'essay';

    public function __construct(
        private int $ass_id,
        private bool $anonymous,
        private HtmlProcessing $html_processing,
        private PdfProcessing $pdf_processing,
        private LanguageService $language,
        private Repositories $repos,
        private WriterReadService $writers,
        private PropetiesReadService $properties,
        private TaskManagerReadService $tasks,
        private UserReadService $users,
    ) {
    }

    public function getAvailableParts(): array
    {
        return [
            new PdfConfigPart(
                "EssayTask",
                'essay',
                self::PART_ESSAY,
                $this->language->txt('pdf_part_essay'),
                true
            ),
        ];
    }

    public function renderPart(string $key, int $task_id, int $writer_id): ?string
    {
        $essay = $this->repos->essay()->oneByWriterIdAndTaskId($writer_id, $task_id);
        if ($essay) {
            return $this->renderEssay($essay);
        }
        return null;
    }

    public function renderEssay(Essay $essay): ?string
    {
        $created = null;
        if (!empty($essay->getWrittenText()) && !$essay->hasPdfFromWrittenText()) {
            $settings = $this->repos->writingSettings()->one($this->ass_id) ?? $this->repos->writingSettings()->new();
            $html = $this->html_processing->getWrittenTextForPdf($essay, $settings);

            $options = (new Options())->withPrintHeader(true)->withPrintFooter(true);
            if ($settings->getAddCorrectionMargin()) {
                $options = $options->withLeftMargin($options->getLeftMargin() + $settings->getLeftCorrectionMargin());
                $options = $options->withRightMargin($options->getRightMargin() + $settings->getRightCorrectionMargin());
            }

            $writer = $this->writers->oneByWriterId($essay->getWriterId());
            $user = $this->users->getUser($writer?->getUserId() ?? 0);

            if ($this->anonymous) {
                $author = $writer->getPseudonym();
            } else {
                $author = $user->getFullname(false);
            }

            $properties = $this->properties->get();

            $title = $author . ' | ' . $properties->getTitle();

            if ($this->tasks->count() > 1) {
                $task = $this->tasks->one($essay->getTaskId());
                $title .= ' - ' . $task->getTitle();
            }

            $options = $options->withTitle($title);
            $options = $options->withSubject($properties->getDescription());
            $options = $options->withAuthor($author);

            $created = $this->pdf_processing->create($html, $options);
        }

        if ($created && $essay->getPdfVersion()) {
            // join the created and the uploaded pdf
            $id = $this->pdf_processing->join([$created, $essay->getPdfVersion()]);
            $this->pdf_processing->cleanup([$created]);
            return $id;
        } elseif ($essay->getPdfVersion()) {
            // return a copy of the uploaded pdf to protect the original from cleanup
            return $this->pdf_processing->copy($essay->getPdfVersion());
        }

        return $created;
    }
}
