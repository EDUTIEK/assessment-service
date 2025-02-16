<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Authentication;

use Edutiek\AssessmentService\Assessment\Data\Token;

interface FullService
{
    public function getTokenExpireTime(Purpose $purpose);
    public function generateApiToken(Purpose $purpose): Token;
    public function checkSignature(Token $token, string $signature) : bool;
    public function checkRemoteAddress(Token $token) : bool;
}