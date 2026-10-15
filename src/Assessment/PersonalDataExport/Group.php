<?php

namespace Edutiek\AssessmentService\Assessment\PersonalDataExport;


use Edutiek\AssessmentService\Assessment\PersonalDataExport\Attributes\Attribute;

class Group
{
    /**
     * @var Attribute[]
     */
    private array $attributes = [];
    /**
     * @var Group[]
     */
    private array $sub_groups = [];

    /**
     * @param string $target_layer
     */
    public function __construct(private readonly string $title) {}


    public function addAttribute(Attribute $attribute) :void
    {
        $this->attributes[] = $attribute;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function addSubGroup(Group $group): void
    {
        $this->sub_groups[] = $group;
    }

    /**
     * @return Group[]
     */
    public function getSubGroups(): array
    {
        return $this->sub_groups;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}