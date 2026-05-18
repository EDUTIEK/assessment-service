<?php

namespace Edutiek\AssessmentService\Task\Data;

enum CorrectorSnippetPurpose: string
{
    case FOR_COMMENT = 'for_comment';
    case FOR_SUMMARY = 'for_summary';
}
