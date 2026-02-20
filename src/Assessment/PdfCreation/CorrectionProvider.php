<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\PdfCreation;

use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;
use Edutiek\AssessmentService\Assessment\Data\PdfFormat;
use Edutiek\AssessmentService\Assessment\Data\PdfSettings;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;
use Edutiek\AssessmentService\Assessment\AssessmentGrading\ReadService as AssessmentGrading;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\System\HtmlProcessing\FullService as HtmlProcessing;
use Edutiek\AssessmentService\System\PdfProcessing\FullService as PdfProcessing;
use Edutiek\AssessmentService\System\PdfCreator\Options;
use Edutiek\AssessmentService\System\User\ReadService as UserService;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingProvider;

readonly class CorrectionProvider implements PdfPartProvider
{
    public const PART_FRONTPAGE = 'frontpage';

    public function __construct(
        private int $ass_id,
        private int $context_id,
        private Repositories $repos,
        private PdfSettings $pdf_settings,
        private CorrectionSettings $correction_settings,
        private AssessmentGrading $assessment_grading,
        private GradingProvider $gradings,
        private HtmlProcessing $html_processing,
        private PdfProcessing $pdf_processing,
        private LanguageService $language,
        private UserService $users
    ) {
    }

    public function getAvailableParts(): array
    {
        return [
            new PdfConfigPart(
                "Assessment",
                self::PART_FRONTPAGE,
                self::PART_FRONTPAGE,
                $this->language->txt('pdf_part_frontpage'),
                false
            ),
        ];
    }

    public function renderPart(
        string $key,
        int $task_id,
        int $writer_id,
        bool $anonymous_writer,
        bool $anonymous_corrector,
        Options $options,
    ): ?string {

        $data = $this->getData($task_id, $writer_id);

        switch ($this->pdf_settings->getFormat()) {
            case PdfFormat::BY:
                $html = $this->fillTemplate($data, 'frontpage_by.html', 'frontpage_by.css');
                return $this->pdf_processing->create($html, (new Options())
                    ->withLeftMargin(40)
                    ->withRightMargin(40)
                    ->withTopMargin(50)
                    ->withPrintHeader(false)
                    ->withPrintFooter(false));
                break;
        }
        return null;
    }

    private function getData(int $task_id, int $writer_id): array
    {
        $data = [];

        $context = $this->repos->contextInfo()->get($this->context_id);
        $data['context'] = [
            'title' => $context->getParentTitle(),
            'description' => $context->getParentDescription()
        ];

        $properties = $this->repos->properties()->one($this->ass_id);
        $data['assessment'] = [
            'title' => $properties?->getTitle(),
            'description' => $properties?->getDescription(),
        ];

        $writer = $this->repos->writer()->one($writer_id);
        $user = $this->users->getUser($writer?->getUserId() ?? 0);

        $data['writer'] = [
            'pseudonym' => $writer?->getPseudonym(),
            'firstname' => $user?->getFirstname(),
            'lastname' => $user?->getLastname(),
            'fullname' => $user?->getFullname(false),
            'login' => $user?->getLogin(),
            'matriculation' => $user?->getMatriculation(),
            'email' => $user?->getEmail()
        ];

        if ($writer?->getFinalPoints() !== null) {
            $level = $this->assessment_grading->getGradLevelForPoints($writer->getFinalPoints());
            $data['result'] = [
                'points' => $writer->getFinalPoints(),
                'grade'=> $level?->getGrade(),
                'statement' => $level?->getStatement(),
                'code' => $level?->getCode(),
            ];
        }

        foreach ($this->gradings->gradingsForTaskAndWriter($task_id, $writer_id) as $grading)
        {
            if ($grading !== null) {
                $corrector_key = match($grading->getPosition()) {
                    GradingPosition::FIRST => 'corrector1',
                    GradingPosition::SECOND => 'corrector2',
                    GradingPosition::STITCH => 'corrector3',
                };
                $corrector = $this->repos->corrector()->one($grading->getCorrectorId());
                $user = $this->users->getUser($corrector?->getUserId() ?? 0);
                $level = $this->assessment_grading->getGradLevelForPoints($grading->getPoints());

                $data[$corrector_key] = [
                    'firstname' => $user?->getFirstname(),
                    'lastname' => $user?->getLastname(),
                    'fullname' => $user?->getFullname(false),
                    'login' => $user?->getLogin(),
                    'matriculation' => $user?->getMatriculation(),
                    'email' => $user?->getEmail(),
                    'points' => $grading->getPoints(),
                    'grade'=> $level?->getGrade(),
                    'statement' => $level?->getStatement(),
                ];
            }
        }

        return $data;
    }

    private function fillTemplate(array $data, string $template_file, ?string $style_file = null): string
    {
        $html = $this->html_processing->fillTemplate(__DIR__ . '/templates/' . $template_file, $data);
        if ($style_file) {
            $style = file_get_contents(__DIR__ . '/templates/' . $style_file);
            $html = "<style>\n$style\n</style>\n$html";
        }
        return $html;
    }
}
