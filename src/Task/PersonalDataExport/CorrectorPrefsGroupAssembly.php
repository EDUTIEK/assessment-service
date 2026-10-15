<?php

namespace Edutiek\AssessmentService\Task\PersonalDataExport;

use Edutiek\AssessmentService\Assessment\PersonalDataExport\AbstractGroupAssembly;
use Edutiek\AssessmentService\Assessment\PersonalDataExport\Group;
use Edutiek\AssessmentService\Task\Data\CorrectorPrefs;

class CorrectorPrefsGroupAssembly extends AbstractGroupAssembly
{

    public function createGroup(): Group
    {
        return new Group("Corrector Preferences");
    }

    public function addCorrectorPrefs(CorrectorPrefs $corrector_prefs): void
    {
        $this->group->addAttribute($this->attribute_factory->number("Corrector Id", $corrector_prefs->getCorrectorId()));
        $this->group->addAttribute($this->attribute_factory->number("Essay Page Zoom", $corrector_prefs->getEssayPageZoom()));
        $this->group->addAttribute($this->attribute_factory->number("Essay Text Zoom", $corrector_prefs->getEssayTextZoom()));
        $this->group->addAttribute($this->attribute_factory->number("Summary Text Zoom", $corrector_prefs->getSummaryTextZoom()));
        
        if ($corrector_prefs->getDefaultShape() !== null) {
            $this->group->addAttribute($this->attribute_factory->text("Default Shape", $corrector_prefs->getDefaultShape()));
        }
        
        $this->group->addAttribute($this->attribute_factory->boolean("Display Labels", $corrector_prefs->getDisplayLabels()));
        $this->group->addAttribute($this->attribute_factory->boolean("Select Words", $corrector_prefs->getSelectWords()));
        
        if ($corrector_prefs->getFilterGradingStatus() !== null) {
            $this->group->addAttribute($this->attribute_factory->text("Filter Grading Status", $corrector_prefs->getFilterGradingStatus()));
        }
        
        if ($corrector_prefs->getFilterCombinedStatus() !== null) {
            $this->group->addAttribute($this->attribute_factory->text("Filter Combined Status", $corrector_prefs->getFilterCombinedStatus()));
        }
        
        if ($corrector_prefs->getFilterAssignedPosition() !== null) {
            $this->group->addAttribute($this->attribute_factory->number("Filter Assigned Position", $corrector_prefs->getFilterAssignedPosition()));
        }
    }
}