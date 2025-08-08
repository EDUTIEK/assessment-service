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

namespace Edutiek\AssessmentService\EssayTask\PdfOutput;

use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\System\Data\ImageDescriptor;
use Closure;

/**
 * Short way to make mutual dependant services work.
 * This should be extracted into multiple services instead.
 */
class LazyService implements FullService
{
    private Closure $get;
    public function __construct(Closure $create)
    {
        $this->get = function () use ($create) {
            $value = $create();
            $this->get = fn() => $value;
            return $value;
        };
    }

    public function getWritingAsPdf(Essay $essay, bool $plainContent = false, bool $onlyText = false): string
    {
        return ($this->get)()->getWritingAsPdf($essay, $plainContent, $onlyText);
    }

    public function getPageImage(string $key): ?ImageDescriptor
    {
        return ($this->get)()->getPageImage($key);
    }
}
