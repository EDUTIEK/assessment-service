<?php

namespace Edutiek\AssessmentService\EssayTask\PersonalDataExport;

use Edutiek\AssessmentService\Assessment\PersonalDataExport\AbstractGroupAssembly;
use Edutiek\AssessmentService\Assessment\PersonalDataExport\Group;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\WriterNotice;
use Edutiek\AssessmentService\EssayTask\Data\WritingStep;

class EssayGroupAssembly extends AbstractGroupAssembly
{

    public function createGroup(): Group
    {
        return new Group("Essay");
    }

    /**
     * @param Essay $essay
     * @return void
     */
    public function addEssay(Essay $essay): void
    {
        $this->addEssayToGroup($this->group, $essay);
    }

    /**
     * @param WriterNotice[] $notices
     * @return void
     */
    public function addWriterNotices(array $notices): void
    {
        $writer_notices_group = new Group("Writer Notices");
        
        foreach ($notices as $notice) {
            $sub_group = new Group("Notice " . $notice->getId());
            $this->addWriterNoticeToGroup($sub_group, $notice);
            $writer_notices_group->addSubGroup($sub_group);
        }
        
        $this->group->addSubGroup($writer_notices_group);
    }

    /**
     * @param WritingStep[] $steps
     * @return void
     */
    public function addWritingSteps(array $steps): void
    {
        $writing_steps_group = new Group("Writing Steps");
        
        foreach ($steps as $step) {
            $sub_group = new Group("Writing Step" . $step->getId());
            $this->addWritingStepToGroup($sub_group, $step);
            $writing_steps_group->addSubGroup($sub_group);
        }
        
        $this->group->addSubGroup($writing_steps_group);
    }

    /**
     * @param Essay $essay
     * @return void
     */
    private function addEssayToGroup(Group $group, Essay $essay): void
    {
        $group->addAttribute($this->attribute_factory->number("Essay Id", $essay->getId()));
        $group->addAttribute($this->attribute_factory->text("Uuid", $essay->getUuid()));
        $group->addAttribute($this->attribute_factory->text("Writer", $this->anonymizeWriter($essay->getWriterId())));
        
        if ($essay->getWrittenText() !== null) {
            $group->addAttribute($this->attribute_factory->text("Written Text", $essay->getWrittenText()));
        }
        
        $group->addAttribute($this->attribute_factory->text("Raw Text Hash", $essay->getRawTextHash()));
        
        if ($essay->getPdfVersion() !== null) {
            $group->addAttribute($this->attribute_factory->file("Pdf Version", $essay->getPdfVersion()));
        }
        
        if ($essay->getLastChange() !== null) {
            $group->addAttribute($this->attribute_factory->dateTime("Last Change", $essay->getLastChange()));
        }
        
        $group->addAttribute($this->attribute_factory->text("Service Version", $essay->getServiceVersion()->value));
        
        if ($essay->getFirstChange() !== null) {
            $group->addAttribute($this->attribute_factory->dateTime("First Change", $essay->getFirstChange()));
        }

        if ($essay->getPdfHash() !== null) {
            $group->addAttribute($this->attribute_factory->text("Pdf Hash", $essay->getPdfHash()));
        }
    }

    /**
     * @param WriterNotice $notice
     * @return void
     */
    private function addWriterNoticeToGroup(Group $group, WriterNotice $notice): void
    {
        $group->addAttribute($this->attribute_factory->number("Note No", $notice->getNoteNo()));
        
        if ($notice->getNoteText() !== null) {
            $group->addAttribute($this->attribute_factory->text("Note Text", $notice->getNoteText()));
        }
        
        if ($notice->getLastChange() !== null) {
            $group->addAttribute($this->attribute_factory->dateTime("Last Change", $notice->getLastChange()));
        }
    }

    /**
     * @param WritingStep $step
     * @return void
     */
    private function addWritingStepToGroup(Group $group, WritingStep $step): void
    {
        if ($step->getTimestamp() !== null) {
            $group->addAttribute($this->attribute_factory->dateTime("Timestamp", $step->getTimestamp()));
        }
        
        if ($step->getContent() !== null) {
            $group->addAttribute($this->attribute_factory->text("Content", $step->getContent()));
        }
        
        $group->addAttribute($this->attribute_factory->boolean("Is Delta", (bool)$step->getIsDelta()));
        
        if ($step->getHashBefore() !== null) {
            $group->addAttribute($this->attribute_factory->text("Hash Before", $step->getHashBefore()));
        }
        
        if ($step->getHashAfter() !== null) {
            $group->addAttribute($this->attribute_factory->text("Hash After", $step->getHashAfter()));
        }
    }
}
