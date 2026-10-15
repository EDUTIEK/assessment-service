<?php

namespace Edutiek\AssessmentService\EssayTask\PersonalDataExport;

use Edutiek\AssessmentService\Assessment\PersonalDataExport\AbstractGroupAssembly;
use Edutiek\AssessmentService\Assessment\PersonalDataExport\Group;
use Edutiek\AssessmentService\EssayTask\Data\WriterPrefs;

class WriterPrefsGroupAssembly extends AbstractGroupAssembly
{

    public function createGroup(): Group
    {
        return new Group("Writer Prefs");
    }

    public function addWriterPrefs(WriterPrefs $prefs): void
    {
        $this->group->addAttribute($this->attribute_factory->number("Instructions Zoom", $prefs->getInstructionsZoom()));
        $this->group->addAttribute($this->attribute_factory->number("Editor Zoom", $prefs->getEditorZoom()));
        $this->group->addAttribute($this->attribute_factory->number("Word Count Enabled", $prefs->getWordCountEnabled()));
        $this->group->addAttribute($this->attribute_factory->number("Word Count Characters", $prefs->getWordCountCharacters()));
    }
}