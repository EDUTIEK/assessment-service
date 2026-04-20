<?php

namespace Edutiek\AssessmentService\Task\Data;

enum PdfMarking: string
{
    case IMAGES = "images";
    case TEXT = "direct";
}
