<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

enum CorrectionStatus: string
{
    /**
     * One or more correctors have to authorize
     */
    case OPEN = 'open';

    /**
     * An approcimation of the correctors is needed
     */
    case APPROXIMATION = 'approximation';

    /**
     * A consulting of the correctors is needed
     */
    case CONSULTING = 'consulting';

    /**
     * A stitch decision by a third corrector is needed
     */
    case STITCH = 'stitch';

    /**
     * The correction is finalized
     */
    case FINALIZED = 'finalized';


    public function isToRevise(): bool
    {
        return $this === self::APPROXIMATION || $this === self::CONSULTING;
    }


    public function languageVariable(): string
    {
        return match ($this) {
            self::OPEN => 'correction_status_open',
            self::APPROXIMATION => 'correction_status_approximation',
            self::CONSULTING => 'correction_status_consulting',
            self::STITCH => 'correction_status_stitch',
            self::FINALIZED => 'correction_status_finalized',
        };
    }
}
