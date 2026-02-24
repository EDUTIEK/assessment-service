<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\PdfCreation;

use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;
use Edutiek\AssessmentService\Assessment\Data\PdfFormat;
use Edutiek\AssessmentService\Assessment\Data\PdfSettings;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;
use Edutiek\AssessmentService\Assessment\AssessmentGrading\ReadService as AssessmentGrading;
use Edutiek\AssessmentService\System\Data\UserData;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\System\HtmlProcessing\FullService as HtmlProcessing;
use Edutiek\AssessmentService\System\PdfProcessing\FullService as PdfProcessing;
use Edutiek\AssessmentService\System\PdfCreator\Options;
use Edutiek\AssessmentService\System\User\ReadService as UserService;
use Edutiek\AssessmentService\System\Format\FullService as FormatService;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskManager;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingProvider;

readonly class CorrectionProvider implements PdfPartProvider
{
    public const PART_FRONTPAGE = 'frontpage';

    public function __construct(
        private int $ass_id,
        private int $user_id,
        private int $context_id,
        private Repositories $repos,
        private OrgaSettings $orga_settings,
        private PdfSettings $pdf_settings,
        private CorrectionSettings $correction_settings,
        private AssessmentGrading $assessment_grading,
        private TaskManager $task_manager,
        private GradingProvider $gradings,
        private HtmlProcessing $html_processing,
        private PdfProcessing $pdf_processing,
        private LanguageService $language,
        private FormatService $format,
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

        $data = $this->getData($task_id, $writer_id, $anonymous_writer, $anonymous_corrector);

        switch ($this->pdf_settings->getFormat()) {
            case PdfFormat::BY:
                $html = $this->fillTemplate($data, 'frontpage_by.html', 'frontpage.css');
                return $this->pdf_processing->create($html, (new Options())
                    ->withLeftMargin(40)
                    ->withRightMargin(40)
                    ->withTopMargin(50)
                    ->withPrintHeader(false)
                    ->withPrintFooter(false));
                break;

            default:
                $html = $this->fillTemplate($data, 'frontpage_edutiek.html', 'frontpage.css');
                return $this->pdf_processing->create($html, (new Options())
                    ->withLeftMargin(20)
                    ->withRightMargin(20)
                    ->withTopMargin(20)
                    ->withPrintHeader(false)
                    ->withPrintFooter(false));
                break;


        }
        return null;
    }

    private function getData(int $task_id, int $writer_id, bool $anonymous_writer, bool $anonymous_corrector): array
    {
        $data = [];

        $data['txt'] = $this->language->all();

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
        $user = ($anonymous_writer ? null : $this->users->getUser($writer?->getUserId() ?? 0));

        $data['writer'] = [
            'anonymous' => $anonymous_writer,
            'pseudonym' => $writer?->getPseudonym(),
            'firstname' => $user?->getFirstname(),
            'lastname' => $user?->getLastname(),
            'fullname' => $user?->getFullname(false) ?? $writer?->getPseudonym(),
            'login' => $user?->getLogin(),
            'matriculation' => $user?->getMatriculation(),
            'email' => $user?->getEmail(),
            'authorization' => $this->format->date($writer?->getWritingAuthorized())
        ];

        $level = $this->assessment_grading->getGradLevelForPoints($writer->getFinalPoints());
        $data['result'] = [
            'points' => $writer->getFinalPoints(),
            'grade' => $level?->getGrade(),
            'statement' => $level?->getStatement(),
            'code' => $level?->getCode(),
            'status' => $this->language->txt((string) ($writer?->getCorrectionStatus()?->languageVariable()))
        ];


        $data['multi_tasks'] = $this->orga_settings->getMultiTasks();
        if ($this->orga_settings->getMultiTasks()) {
            foreach ($this->task_manager->all() as $task) {
                $gradings = $this->gradings->gradingsForTaskAndWriter($task_id, $writer_id);
                $grading = reset($gradings);
                $data['tasks'][] = [
                    'title' => $task->getTitle(),
                    'weight' => $task->getWeight(),
                    'points' => $grading ? $grading->getPoints() : null,
                ];
            }
        }

        $data['correctors'] = [];
        foreach ($this->gradings->gradingsForTaskAndWriter($task_id, $writer_id) as $grading) {
            if ($grading !== null) {
                $corrector_key = match($grading->getPosition()) {
                    GradingPosition::FIRST => 'corrector1',
                    GradingPosition::SECOND => 'corrector2',
                    GradingPosition::STITCH => 'corrector3',
                };

                $position = $this->language->txt(
                    $this->correction_settings->hasMultipleCorrectors()
                        ? $this->language->txt($grading->getPosition()->languageVariable())
                        : ($this->orga_settings->getMultiTasks()
                            ? $this->task_manager->one($task_id)->getTitle()
                                : $this->language->txt('correction'))
                );

                $corrector = $this->repos->corrector()->one($grading->getCorrectorId());
                $user = $anonymous_corrector ? null : $this->users->getUser($corrector?->getUserId() ?? 0);
                $level = $this->assessment_grading->getGradLevelForPoints($grading->getPoints());

                $show_grading = $grading->isAuthorized() || $grading->isRevised() || $corrector?->getUserId() == $this->user_id;

                $data['correctors'][] = $data[$corrector_key] = [
                    'anonymous' => $anonymous_corrector,
                    'position' => $position,
                    'authorized' => $grading->isAuthorized(),
                    'firstname' => $user?->getFirstname(),
                    'lastname' => $user?->getLastname(),
                    'fullname' => $user?->getFullname(false),
                    'login' => $user?->getLogin(),
                    'matriculation' => $user?->getMatriculation(),
                    'email' => $user?->getEmail(),
                    'points' => $show_grading ? $grading->getPoints() : null,
                    'grade' => $show_grading ? $level?->getGrade() : null,
                    'statement' => $show_grading ? $level?->getStatement() : null,
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
