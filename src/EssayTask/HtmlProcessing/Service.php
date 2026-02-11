<?php

namespace Edutiek\AssessmentService\EssayTask\HtmlProcessing;

use DOMDocument;
use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings as CorrectionSettings;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\WritingSettings;
use Edutiek\AssessmentService\System\HtmlProcessing\FullService as SystemHtmlProcessing;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\Task\CorrectorComment\CorrectorCommentInfo;
use Edutiek\AssessmentService\Task\CorrectorComment\InfoService as CommentsService;

/**
 * Tool for processing HTML code coming from the rich text editor
 */
class Service implements FullService
{
    public const COLOR_NORMAL = '#D8E5F4';

    /**
     * A static instance is needed for calls from XSLT
     * It must be set to the current object before
     */
    public static self $instance;

    /**
     * All Comments that should be merged
     * @var CorrectorCommentInfo[]
     */
    private array $all_infos = [];

    /**
     * Comments for the current paragraph
     * @var CorrectorCommentInfo[]
     */
    private array $current_infos = [];

    public function __construct(
        private readonly WritingSettings $writing_settings,
        private readonly CorrectionSettings $correction_settings,
        private readonly CommentsService $comments_service,
        private readonly SystemHtmlProcessing $processor,
        private readonly LanguageService $lang
    ) {
    }

    public function getWrittenTextForCorrection(?Essay $essay): string
    {
        return $this->processor->getContentForMarking(
            (string) $essay->getWrittenText(),
            $this->writing_settings->getAddParagraphNumbers(),
            $this->writing_settings->getHeadlineScheme()
        );
    }

    public function getWrittenTextForPdf(?Essay $essay): string
    {
        return $this->processor->getContentForPdf(
            (string) $essay->getWrittenText(),
            $this->writing_settings->getAddParagraphNumbers(),
            $this->writing_settings->getHeadlineScheme()
        );
    }

    public function getCorrectedTextForPdf(?Essay $essay, array $infos): string
    {
        self::$instance = $this;
        $this->all_infos = $infos;
        $this->current_infos = [];

        $html = $this->processor->getContentForMarking(
            (string) $essay->getWrittenText(),
            $this->writing_settings->getAddParagraphNumbers(),
            $this->writing_settings->getHeadlineScheme()
        );

        $html = $this->processor->processXslt(
            $this->processor->replaceCustomMarkup($html),
            __DIR__ . '/xsl/comments.xsl',
            $essay ? $essay->getServiceVersion() : 0,
            $this->writing_settings->getAddParagraphNumbers(),
        );

        $html = $this->processor->getContentStyles(
            $this->writing_settings->getAddParagraphNumbers(),
            $this->writing_settings->getHeadlineScheme())
            . "<style>\n" . file_get_contents(__DIR__ . '/styles/correction.css') . "\n</style>\n"
            . $html;

        return $html;
    }

    /**
     * @param CorrectorCommentInfo[] $infos
     * @return string
     */
    public function getCommentsHtml(array $infos): string
    {
        $html = '';
        foreach ($infos as $info) {
            if ($info->hasDetailsToShow()) {
                $content = $this->quote($info->getLabel());
                if ($this->correction_settings->hasMultipleCorrectors()) {
                    $content .= ' ' . $info->getPositionText();
                }

                $color = $this->getTextBackgroundColor([$info]);
                $content = '<strong style="background-color:' . $color . ';">' . $content . '</strong>';

                if ($info->getRatingText()) {
                    $content .= ' ' . $info->getRatingText();
                }

                if (!empty($info->getComment()->getComment())) {
                    $content .= ' ' . $this->quote($info->getComment()->getComment());
                }

                $points = $info->getPoints();
                if ($points == 1) {
                    $content .= '<br>(' . $this->lang->txt('1_point') . ')';
                } elseif ($points != 0) {
                    $content .= '<br />(' . sprintf($this->lang->txt('x_points'), $points) . ')';
                }

                $content = '<p>' . $content . '</p>';

                $html .= $content . "\n";

                // remove ascii control characters except tab, cr and lf
                $html = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $html);
            }
        }
        return $html;
    }

    private function quote($html): string
    {
        return htmlspecialchars($html, ENT_NOQUOTES, 'UTF-8');
    }

    /**
     * @param CorrectorCommentInfo[] $infos
     * @todo: use corrector colors
     */
    private function getTextBackgroundColor(array $infos): string
    {
        if (!empty($infos)) {
            return self::COLOR_NORMAL;
        }
        return '';
    }

    /**
     * Initialize the collection of comments for the current paragraph
     */
    public static function initCurrentComments(string $paraNumber)
    {
        self::$instance->current_infos = self::$instance->comments_service->filterAndLabelInfos(
            self::$instance->all_infos,
            (int) $paraNumber,
        );
    }

    /**
     * Get a label if a comment starts at the given word
     */
    public static function commentLabel(string $wordNumber): string
    {
        $labels = [];
        foreach (self::$instance->current_infos as $info) {
            if ((int) $wordNumber == $info->getComment()->getStartPosition() && !empty($info->getLabel())) {
                $labels[] = $info->getLabel();
            }
        }
        return (implode(', ', $labels));
    }

    /**
     * Get the background color for the word
     */
    public static function commentColor(string $wordNumber): string
    {
        $infos = [];
        foreach (self::$instance->current_infos as $info) {
            if ((int) $wordNumber >= $info->getComment()->getStartPosition() && (int) $wordNumber <= $info->getComment()->getEndPosition()) {
                $infos[] = $info;
            }
        }
        return self::$instance->getTextBackgroundColor($infos);
    }

    /**
     * Get the comments for the current paragraph
     * @return \DOMElement
     * @throws \DOMException
     */
    public static function getCurrentComments(): \DOMElement
    {
        $html = self::$instance->getCommentsHtml(self::$instance->current_infos);

        $doc = new DOMDocument();
        $doc->loadXML('<root xml:id="root">' . $html . '</root>');
        return $doc->getElementById('root');
    }
}
