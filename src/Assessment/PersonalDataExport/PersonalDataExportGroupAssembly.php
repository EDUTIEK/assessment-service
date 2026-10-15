<?php

namespace Edutiek\AssessmentService\Assessment\PersonalDataExport;

use Edutiek\AssessmentService\Assessment\PersonalDataExport\AbstractGroupAssembly;
use Edutiek\AssessmentService\System\Data\UserData;

class PersonalDataExportGroupAssembly extends AbstractGroupAssembly
{

    public function createGroup(): Group
    {
        return new Group("Personal Data Export");
    }

    public function addUser(UserData $user): void
    {
        $user_group = new Group("User");
        $user_group->addAttribute($this->attribute_factory->number("User Id", $user->getId()));
        $user_group->addAttribute($this->attribute_factory->text("Login", $user->getLogin()));
        $user_group->addAttribute($this->attribute_factory->text("Title", $user->getTitle()));
        $user_group->addAttribute($this->attribute_factory->text("First Name", $user->getFirstName()));
        $user_group->addAttribute($this->attribute_factory->text("Last Name", $user->getLastName()));
        $user_group->addAttribute($this->attribute_factory->text("Email", $user->getEmail()));
        $user_group->addAttribute($this->attribute_factory->text("Initials", $user->getInitials()));
        $user_group->addAttribute($this->attribute_factory->text("Matriculation", $user->getMatriculation()));
        $user_group->addAttribute($this->attribute_factory->text("Timezone", $user->getTimezone()->getName()));

        $this->group->addSubGroup($user_group);
    }

    public function addAssessmentGroups(array $groups){
        $assessment_group = new Group("Assessments");
        array_map(fn(Group $group) => $assessment_group->addSubGroup($group), $groups);

        $this->addSubGroup($assessment_group);
    }
}