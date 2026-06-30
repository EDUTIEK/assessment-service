<?php declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfProcessing;

use Exception;

class PdfUniteException extends Exception
{
    public function __construct(string $message, $value = null)
    {
        $desc = array_key_exists(1, func_get_args()) ?
            (': ' . var_export($value, true))
            : '';

        parent::__construct($message . $desc);
    }
}
