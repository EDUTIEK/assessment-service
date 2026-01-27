<?php

namespace Edutiek\AssessmentService\EssayTask\HtmlProcessing;

use DOMDocument;
use Edutiek\AssessmentService\EssayTask\Comments\Service as CommentsService;
use Edutiek\AssessmentService\Task\Data\CorrectionSettings;
use Edutiek\AssessmentService\Task\Data\CorrectorComment;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\WritingSettings;
use Edutiek\AssessmentService\System\HtmlProcessing\FullService as SystemHtmlProcessing;

/**
 * Tool for processing HTML code coming from the rich text editor
 */
class Service implements FullService
{
    public const COLOR_NORMAL = '#D8E5F4';
    public const COLOR_EXCELLENT = '#E3EFDD';
    public const COLOR_CARDINAL = '#FBDED1';

    public static CommentsService $comments_service;
    public static ?WritingSettings $writingSettings = null;
    public static ?CorrectionSettings $correctionSettings = null;

    /**
     * All Comments that should be merged
     * @var CorrectorComment[]
     */
    public static array $allComments = [];

    /**
     * Comments for the current paragraph
     * @var CorrectorComment[]
     */
    public static array $currentComments = [];

    public function __construct(
        CommentsService $comments_service,
        private SystemHtmlProcessing $processor
    ) {
        self::$comments_service = $comments_service;
    }

    public function getWrittenTextForCorrection(?Essay $essay, WritingSettings $settings): string
    {
        return $this->processor->getContentForMarking(
            (string) $essay->getWrittenText(),
            $settings->getAddParagraphNumbers(),
            $settings->getHeadlineScheme()
        );
    }

    public function getWrittenTextForPdf(?Essay $essay, WritingSettings $settings): string
    {
        return $this->processor->getContentForPdf(
            (string) $essay->getWrittenText(),
            $settings->getAddParagraphNumbers(),
            $settings->getHeadlineScheme()
        );
    }

    public function getCorrectedTextForPdf(
        ?Essay $essay,
        WritingSettings $writingSettings,
        CorrectionSettings $correctionSettings,
        array $comments
    ): string {

        self::$writingSettings = $writingSettings;
        self::$correctionSettings = $correctionSettings;
        self::$allComments = $comments;
        self::$currentComments = [];

        $html = $this->processor->getContentForPdf(
            (string) $essay->getWrittenText(),
            $writingSettings->getAddParagraphNumbers(),
            $writingSettings->getHeadlineScheme()
        );

        // todo: refer to local functions in the xsl file
        $html = $this->processor->processXslt(
            $html,
            __DIR__ . '/xsl/pdf_comments.xsl',
            $essay ? $essay->getServiceVersion() : 0
        );

        return $html;
    }

    /**
     * Initialize the collection of comments for the current paragraph
     */
    public static function initCurrentComments(string $paraNumber)
    {
        self::$currentComments = self::$comments_service->getSortedCommentsOfParent(
            self::$allComments,
            (int) $paraNumber
        );
    }

    /**
     * Get a label if a comment starts at the given word
     */
    public static function commentLabel(string $wordNumber): string
    {
        $labels = [];
        foreach (self::$currentComments as $comment) {
            if ((int) $wordNumber == $comment->getStartPosition() && !empty($comment->getLabel())) {
                $labels[] = $comment->getLabel();
            }
        }
        return (implode(',', $labels));
    }

    /**
     * Get the background color for the word
     */
    public static function commentColor(string $wordNumber): string
    {
        $comments = [];
        foreach (self::$currentComments as $comment) {
            if ((int) $wordNumber >= $comment->getStartPosition() && (int) $wordNumber <= $comment->getEndPosition()) {
                $comments[] = $comment;
            }
        }
        return self::$comments_service->getTextBackgroundColor($comments);
    }

    /**
     * Get the comments for the current paragraph
     * @return \DOMElement
     * @throws \DOMException
     */
    public static function getCurrentComments(): \DOMElement
    {
        $html = self::$comments_service->getCommentsHtml(self::$currentComments, self::$correctionSettings);

        $doc = new DOMDocument();
        $doc->loadXML('<root xml:id="root">' . $html . '</root>');
        return $doc->getElementById('root');
    }
}
