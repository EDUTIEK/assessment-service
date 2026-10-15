<?php

namespace Edutiek\AssessmentService\Task\PersonalDataExport;

use Edutiek\AssessmentService\Assessment\PersonalDataExport\AbstractGroupAssembly;
use Edutiek\AssessmentService\Assessment\PersonalDataExport\Group;
use Edutiek\AssessmentService\Task\Data\CorrectorTaskPrefs;
use Edutiek\AssessmentService\Task\Data\CorrectorTemplate;
use Edutiek\AssessmentService\Task\Data\WriterAnnotation;
use Edutiek\AssessmentService\Task\Data\Settings;
use Edutiek\AssessmentService\Task\Data\RatingCriterion;

class TaskGroupAssembly extends AbstractGroupAssembly
{
    public function createGroup(): Group
    {
        return new Group("Task " . $this->usage_scope->getTaskId());
    }

    /**
     * @param Settings $settings
     * @return void
     */
    public function addTaskSettings(Settings $settings): void
    {
        $this->group->addAttribute($this->attribute_factory->number("ID", $settings->getTaskId()));
        $this->group->addAttribute($this->attribute_factory->text("Title", $settings->getTitle()));
    }

    /**
     * @param CorrectorTaskPrefs $corrector_task_prefs
     * @return void
     */
    public function addCorrectorTaskPrefs(CorrectorTaskPrefs $corrector_task_prefs): void
    {
        $corrector_task_prefs_group = new Group("Corrector Task Prefs");
        $corrector_task_prefs_group->addAttribute($this->attribute_factory->number("Corrector Id", $corrector_task_prefs->getCorrectorId()));
        $corrector_task_prefs_group->addAttribute($this->attribute_factory->boolean("Criterion Copy", $corrector_task_prefs->getCriterionCopy()));

        $this->group->addSubGroup($corrector_task_prefs_group);
    }

    /**
     * @param RatingCriterion[] $rating_criterion
     * @return void
     */
    public function addOwnRatingCriterions(array $rating_criterion): void
    {
        $group = new Group("Own Rating Criterion");
        foreach($rating_criterion as $criterion) {
            $sub_group = new Group($criterion->getId());
            $this->addRatingCriterionToGroup($sub_group, $criterion);
            $group->addSubGroup($sub_group);
        }
        $this->group->addSubGroup($group);
    }

    /**
     * @param CorrectorTemplate $corrector_templates
     * @return void
     */
    public function addCorrectorTemplates(CorrectorTemplate $corrector_template): void
    {
        $corrector_templates_group = new Group("Corrector Template");

        $corrector_templates_group->addAttribute($this->attribute_factory->number("Corrector Id", $corrector_template->getCorrectorId()));
        #$corrector_templates_group->addAttribute($this->attribute_factory->number("Task Id", $corrector_template->getTaskId()));
        $corrector_templates_group->addAttribute($this->attribute_factory->boolean("Shared", $corrector_template->getShared()));
        $corrector_templates_group->addAttribute($this->attribute_factory->text("Content", $corrector_template->getContent()));

        $this->group->addSubGroup($corrector_templates_group);
    }

    /**
     * @param WriterAnnotation[] $writer_annotations
     * @return void
     */
    public function addWriterAnnotations(array $writer_annotations): void
    {
        $writer_annotations_group = new Group("Writer Annotations");
        /**
         * @var WriterAnnotation $writer_annotation
         */
        foreach ($writer_annotations as $writer_annotation) {
            $sub_group = new Group((string) $writer_annotation->getId());
            #$sub_group->addAttribute($this->attribute_factory->number("Task Id", $writer_annotation->getTaskId()));
            $sub_group->addAttribute($this->attribute_factory->number("Writer Id", $writer_annotation->getWriterId()));
            $sub_group->addAttribute($this->attribute_factory->number("Resource Id", $writer_annotation->getResourceId()));
            $sub_group->addAttribute($this->attribute_factory->text("Mark Key", $writer_annotation->getMarkKey()));
            $sub_group->addAttribute($this->attribute_factory->text("Mark Value", $writer_annotation->getMarkValue()));
            $sub_group->addAttribute($this->attribute_factory->text("Comment", $writer_annotation->getComment()));
            $sub_group->addAttribute($this->attribute_factory->number("Parent Number", $writer_annotation->getParentNumber()));
            $sub_group->addAttribute($this->attribute_factory->number("Start Position", $writer_annotation->getStartPosition()));
            $sub_group->addAttribute($this->attribute_factory->number("End Position", $writer_annotation->getEndPosition()));
            $writer_annotations_group->addSubGroup($sub_group);
        }
        $this->group->addSubGroup($writer_annotations_group);
    }

    private function addRatingCriterionToGroup(Group $group, RatingCriterion $criterion_point): void
    {
        $group->addAttribute($this->attribute_factory->number("ID", $criterion_point->getId()));
        $group->addAttribute($this->attribute_factory->text("Title", $criterion_point->getTitle()));
        if ($criterion_point->getDescription() !== null) {
            $group->addAttribute($this->attribute_factory->text("Description", $criterion_point->getDescription()));
        }
        $group->addAttribute($this->attribute_factory->number("Points", $criterion_point->getPoints()));
        $group->addAttribute($this->attribute_factory->number("Task Id", $criterion_point->getTaskId()));
        $group->addAttribute($this->attribute_factory->number("General", $criterion_point->getGeneral()));
    }
}