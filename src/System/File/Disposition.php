<?php

namespace Edutiek\AssessmentService\System\File;

enum Disposition: string
{
    case ATTACHMENT = 'attachment';
    case INLINE = 'inline';
}
