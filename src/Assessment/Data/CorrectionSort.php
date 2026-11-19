<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

enum CorrectionSort: string
{
    case GRADING_POSITION = 'grading_position';
    case OWN_STATE = 'own_state';
    case PSEUDONYM_ASC = 'pseudonym_asc';
    case PSEUDONYM_DESC = 'pseudonym_desc';
    case MATRICULATION_ASC = 'matriculation_asc';
    case MATRICULATION_DESC = 'matriculation_desc';

    public function langVar()
    {
        return 'correction_sort_' . $this->value;
    }
}
