<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace Edutiek\AssessmentService\Model;

use Edutiek\AssessmentService\Attribute\Table;
use Edutiek\AssessmentService\Attribute\Column;
use Edutiek\AssessmentService\Attribute\Sequence;
use Edutiek\AssessmentService\Attribute\Key;
use DateTimeImmutable;

#[Table(name: 'dummy')]
class Dummy
{
    // Infer db type from php type.
    private string $foo = 'fofof';
    #[Key]
    private int $bar = 3484;
    private float $baz = 4.0;
    private DateTimeImmutable $some_day;

    #[Column(type: 'integer')] // Specify db type explicitly.
    #[Sequence] // Define sequence.
    private string $hej;

    #[Column(name: 'hu')] // Specify db field name explicitly.
    private string $ho = 'huhuhu';

    public function setSomeDay(DateTimeImmutable $some_day): void
    {
        $this->some_day = $some_day;
    }

    public function setHej(string $hej): void
    {
        $this->hej = $hej;
    }
}
