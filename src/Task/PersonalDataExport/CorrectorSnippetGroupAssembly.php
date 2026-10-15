<?php

namespace Edutiek\AssessmentService\Task\PersonalDataExport;

use Edutiek\AssessmentService\Assessment\PersonalDataExport\AbstractGroupAssembly;
use Edutiek\AssessmentService\Assessment\PersonalDataExport\Group;
use Edutiek\AssessmentService\Task\Data\CorrectorSnippet;

class CorrectorSnippetGroupAssembly extends AbstractGroupAssembly
{

    public function createGroup(): Group
    {
        return new Group("Corrector Snippets");
    }

    /**
     * @param CorrectorSnippet[] $corrector_snippets
     * @return void
     */
    public function addCorrectorSnippets(array $corrector_snippets): void
    {
        /**
         * @var CorrectorSnippet $corrector_snippet
         */
        foreach ($corrector_snippets as $corrector_snippet) {
            $sub_group = new Group($corrector_snippet->getId());
            $sub_group->addAttribute($this->attribute_factory->number("ID", $corrector_snippet->getId()));
            $sub_group->addAttribute($this->attribute_factory->text("Shortcut", $corrector_snippet->getShortcut()));
            $sub_group->addAttribute($this->attribute_factory->text("Text", $corrector_snippet->getText()));
            $sub_group->addAttribute($this->attribute_factory->text("Purpose", $corrector_snippet->getPurpose()->value));
            $sub_group->addAttribute($this->attribute_factory->number("Corrector Id", $corrector_snippet->getCorrectorId()));

            $this->group->addSubGroup($sub_group);
        }
    }
}
