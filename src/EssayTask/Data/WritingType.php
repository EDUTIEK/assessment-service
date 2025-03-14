<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\EssayTask\Data;

enum WritingType: string
{
    case ESSAY_EDITOR = 'essay_editor';
    case PDF_UPLOAD = 'pdf_upload';
}
