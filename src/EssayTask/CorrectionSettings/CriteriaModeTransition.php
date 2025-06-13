<?php

namespace Edutiek\AssessmentService\EssayTask\CorrectionSettings;

use Edutiek\AssessmentService\EssayTask\Data\CriteriaMode;

enum CriteriaModeTransition: string
{
    case NoneToNone = 'nonenone';
    case NoneToFixed = 'nonefixed';
    case NoneToCorrector = 'nonecorr';
    case FixedToFixed = 'fixedfixed';
    case FixedToNone = 'fixednone';
    case FixedToCorrector = 'fixedcorr';
    case CorrectorToCorrector = 'corrcorr';
    case CorrectorToNone = 'corrnone';
    case CorrectorToFixed = 'corrfixed';

    public function fromTransition(CriteriaMode $a, CriteriaMode $b) : self
    {
       return self::from($a->value . $b->value);
    }
}