<?php

namespace Edutiek\AssessmentService\EssayTask\HtmlProcessing;

use DOMDocument;
use Edutiek\AssessmentService\EssayTask\Data\CommentRating;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\WritingSettings;
use Edutiek\AssessmentService\System\HtmlProcessing\FullService as SystemHtmlProcessing;
use Edutiek\AssessmentService\Task\CorrectorComment\ReadService as CommentsService;
use Edutiek\AssessmentService\Task\Data\CorrectionSettings;
use Edutiek\AssessmentService\Task\Data\CorrectorComment;

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
     * @var CorrectorComment[]
     */
    private array $all_comments = [];

    /**
     * Comments for the current paragraph
     * @var CorrectorComment[]
     */
    private array $current_comments = [];

    public function __construct(
        private readonly WritingSettings $writing_settings,
        private readonly CorrectionSettings $correction_settings,
        private readonly CommentsService $comments_service,
        private readonly SystemHtmlProcessing $processor
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

    public function getCorrectedTextForPdf(?Essay $essay, array $comments): string
    {
        $html = $this->processor->getContentForPdf(
            (string) $essay->getWrittenText(),
            $this->writing_settings->getAddParagraphNumbers(),
            $this->writing_settings->getHeadlineScheme()
        );

        self::$instance = $this;
        $this->all_comments = $comments;
        $this->current_comments = [];

        $html = $this->processor->processXslt(
            $html,
            __DIR__ . '/xsl/comments.xsl',
            $essay ? $essay->getServiceVersion() : 0
        );

        return $html;
    }

    public function getCommentsHtml(array $comments): string
    {
        $html = '';
        foreach ($comments as $comment) {
            if ($comment->hasDetailsToShow()) {
                $content = $comment->getLabel();
                if ($comment->showRating()) {

                    $content = $comment->getLabel();
                }
                if ($comment->showRating() && $comment->getRating() == CommentRating::CARDINAL->value) {
                    $content .= ' ' . $this->correction_settings->getNegativeRating();
                }
                if ($comment->showRating() && $comment->getRating() == CommentRating::EXCELLENT->value) {
                    $content .= ' ' . $this->correction_settings->getPositiveRating();
                }

                $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');

                $color = $this->getTextBackgroundColor([$comment]);
                $content = '<strong style="background-color:' . $color . ';">' . $content . '</strong>';

                if (!empty($comment->getComment())) {
                    $content .= ' ' . htmlspecialchars($comment->getComment(), ENT_NOQUOTES, 'UTF-8');
                }

                if ($comment->showPoints() && $comment->getPoints() == 1) {
                    $content .= '<br />(1 Punkt)';
                } elseif ($comment->showPoints() && $comment->getPoints() != 0) {
                    $content .= '<br />(' . $comment->getPoints() . ' Punkte)';
                }

                $content = '<p style="font-family: sans-serif; font-size:10px;">' . $content . '</p>';

                $html .= $content . "\n";

                // remove ascii control characters except tab, cr and lf
                $html = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $html);
            }
        }
        return $html;
    }

    /**
     * @param CorrectorComment[] $comments
     * @todo: use corrector colors
     */
    private function getTextBackgroundColor(array $comments): string
    {
        return self::COLOR_NORMAL;
    }

    /**
     * Initialize the collection of comments for the current paragraph
     */
    public static function initCurrentComments(string $paraNumber)
    {
        self::$instance->current_comments = self::$instance->comments_service->filterAndLabel(
            self::$instance->all_comments,
            (int) $paraNumber
        );
    }

    /**
     * Get a label if a comment starts at the given word
     */
    public static function commentLabel(string $wordNumber): string
    {
        $labels = [];
        foreach (self::$instance->current_comments as $comment) {
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
        foreach (self::$instance->current_comments as $comment) {
            if ((int) $wordNumber >= $comment->getStartPosition() && (int) $wordNumber <= $comment->getEndPosition()) {
                $comments[] = $comment;
            }
        }
        return self::getTextBackgroundColor($comments);
    }

    /**
     * Get the comments for the current paragraph
     * @return \DOMElement
     * @throws \DOMException
     */
    public static function getCurrentComments(): \DOMElement
    {
        $html = self::$instance->getCommentsHtml(self::$instance->current_comments);

        $doc = new DOMDocument();
        $doc->loadXML('<root xml:id="root">' . $html . '</root>');
        return $doc->getElementById('root');
    }
}
