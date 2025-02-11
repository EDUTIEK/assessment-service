<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

enum CommentRating: string
{
    case CARDINAL = 'cardinal';
    case EXCELLENT = 'excellent';
}
