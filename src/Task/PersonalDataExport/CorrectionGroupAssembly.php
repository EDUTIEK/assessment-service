<?php

namespace Edutiek\AssessmentService\Task\PersonalDataExport;

use Edutiek\AssessmentService\Assessment\PersonalDataExport\AbstractGroupAssembly;
use Edutiek\AssessmentService\Assessment\PersonalDataExport\Group;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use Edutiek\AssessmentService\Task\Data\CorrectorComment;
use Edutiek\AssessmentService\Task\Data\CorrectorPoints;
use Edutiek\AssessmentService\Task\Data\RatingCriterion;

class CorrectionGroupAssembly extends AbstractGroupAssembly
{
    /**
     * @var RatingCriterion[]
     */
    private array $rating_criterion = [];
    public function createGroup(): Group
    {
        return new Group("Corrections");
    }

    /**
     * @param RatingCriterion[] $rating_criterion
     * @return void
     */
    public function setRatingCriterion(array $rating_criterion)
    {
        foreach($rating_criterion as $criterion) {
            $this->rating_criterion[$criterion->getId()] = $criterion;
        }
    }

    /**
     * @param CorrectorAssignment   $assignment
     * @param CorrectorSummary|null $summary
     * @param CorrectorComment[]    $comments
     * @param CorrectorPoints[]     $points
     * @return void
     */
    public function addCorrection(CorrectorAssignment $assignment, ?CorrectorSummary $summary, array $comments, array $points): void
    {
        $group = new Group("Correction " . $assignment->getId());

        $this->addCorrectionToGroup($group, $assignment);

        if ($summary !== null) {
            $summary_group = new Group("Corrector Summary");
            $this->addCorrectorSummaryToGroup($summary_group, $summary);
            $group->addSubGroup($summary_group);
        }


        $points_by_comment = [];
        foreach ($points as $point) {
            $points_by_comment[$point->getCommentId()] = $point;
        }


        foreach ($comments as $comment) {
            $point = $points_by_comment[$comment->getId()] ?? null;
            $comment_group = new Group("Comment " . $comment->getId());
            $this->addCorrectorCommentToGroup($comment_group, $comment);
            if ($point !== null) {
                $this->addCorrectorPointToGroup($comment_group, $point);
            }
            $group->addSubGroup($comment_group);
        }
        $this->group->addSubGroup($group);
    }
    /**
     * Factory: Create a Group for a CorrectorAssignment
     */
    private function addCorrectionToGroup(Group $group, CorrectorAssignment $assignment): void
    {
        $writer = $this->anonymizeWriter($assignment->getWriterId());
        $corrector = $this->anonymizeCorrector($assignment->getCorrectorId());

        #$group->addAttribute($this->attribute_factory->number("Task Id", $assignment->getTaskId()));
        $group->addAttribute($this->attribute_factory->text("Writer", $writer));
        $group->addAttribute($this->attribute_factory->text("Corrector", $corrector));
        $group->addAttribute($this->attribute_factory->text("Position", $assignment->getPosition()->name));
    }


    /**
     * Factory: Create a Group for a CorrectorSummary
     */
    private function addCorrectorSummaryToGroup(Group $group, CorrectorSummary $summary): void
    {
        $writer = $this->anonymizeWriter($summary->getWriterId());
        $corrector = $this->anonymizeCorrector($summary->getCorrectorId());
        
        $group->addAttribute($this->attribute_factory->text("Writer", $writer));
        $group->addAttribute($this->attribute_factory->text("Corrector", $corrector));
        
        if ($summary->getSummaryText() !== null) {
            $group->addAttribute($this->attribute_factory->text("Summary Text", $summary->getSummaryText()));
        }

        if($summary->getSummaryPdf() !== null) {
            $group->addAttribute($this->attribute_factory->file("Summary PDF", $summary->getSummaryPdf()));
        }

        if ($summary->getPoints() !== null) {
            $group->addAttribute($this->attribute_factory->number("Points", $summary->getPoints()));
        }
        
        if ($summary->getLastChange() !== null) {
            $group->addAttribute($this->attribute_factory->dateTime("Last Change", $summary->getLastChange()));
        }
        
        if ($summary->getCorrectionAuthorized() !== null) {
            $group->addAttribute($this->attribute_factory->dateTime("Correction Authorized", $summary->getCorrectionAuthorized()));
        }
        
        if ($summary->getCorrectionAuthorizedBy() !== null) {
            $authorized_by = $this->anonymizeUser($summary->getCorrectionAuthorizedBy());
            $group->addAttribute($this->attribute_factory->text("Correction Authorized By", $authorized_by));
        }
        
        if ($summary->getPreGraded() !== null) {
            $group->addAttribute($this->attribute_factory->dateTime("Pre Graded", $summary->getPreGraded()));
        }
        
        if ($summary->getRevised() !== null) {
            $group->addAttribute($this->attribute_factory->dateTime("Revised", $summary->getRevised()));
        }
        
        if ($summary->getRevisionText() !== null) {
            $group->addAttribute($this->attribute_factory->text("Revision Text", $summary->getRevisionText()));
        }

        if ($summary->getRevisionPoints() !== null) {
            $group->addAttribute($this->attribute_factory->number("Revision Points", $summary->getRevisionPoints()));
        }

        $group->addAttribute($this->attribute_factory->boolean("Require Other Revision", $summary->getRequireOtherRevision()));
        
        $group->addAttribute($this->attribute_factory->text("Grading Status", $summary->getGradingStatus()->value));
    }

    /**
     * Factory: Create a Group for a CorrectorComment
     */
    private function addCorrectorCommentToGroup(Group $group, CorrectorComment $comment): void
    {
        $writer = $this->anonymizeWriter($comment->getWriterId());
        $corrector = $this->anonymizeCorrector($comment->getCorrectorId());
        
        $group->addAttribute($this->attribute_factory->text("Key", $comment->getKey()));
        $group->addAttribute($this->attribute_factory->text("Writer", $writer));
        
        if ($comment->getComment() !== null) {
            $group->addAttribute($this->attribute_factory->text("Comment", $comment->getComment()));
        }
        
        $group->addAttribute($this->attribute_factory->number("Start Position", $comment->getStartPosition()));
        $group->addAttribute($this->attribute_factory->number("End Position", $comment->getEndPosition()));
        $group->addAttribute($this->attribute_factory->text("Rating", $comment->getRating()));
        $group->addAttribute($this->attribute_factory->text("Corrector", $corrector));
        $group->addAttribute($this->attribute_factory->number("Parent Number", $comment->getParentNumber()));
        
        if ($comment->getMarks() !== null) {
            $group->addAttribute($this->attribute_factory->text("Marks", $comment->getMarks()));
        }
    }

    /**
     * Factory: Create a Group for a CorrectorPoints
     */
    private function addCorrectorPointToGroup(Group $group, CorrectorPoints $point): void
    {
        $group->addAttribute($this->attribute_factory->text("Key", $point->getKey()));

        if ($point->getCriterionId() !== null) {
            $rating_criterion = $this->rating_criterion[$point->getCriterionId()] ?? null;
            $group->addAttribute($this->attribute_factory->text("Rating Criterion", $rating_criterion?->getTitle() ?? $point->getCriterionId()));
            if($rating_criterion !== null) {
                $group->addAttribute($this->attribute_factory->text("Rating Criterion Points", $rating_criterion->getPoints()));
            }
        }

        $group->addAttribute($this->attribute_factory->number("Points", $point->getPoints()));
    }
}
