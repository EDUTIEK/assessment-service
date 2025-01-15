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

namespace Edutiek\AssessmentService\EssayTask\Data;

abstract class CorrectorAssignmentPreference implements EssayTaskEntity
{
    public abstract function getCorrectorId(): int;
    public abstract function setCorrectorId(int $corrector_id): void;
    public abstract function getEssayPageZoom(): float;
    public abstract function setEssayPageZoom(float $essay_page_zoom): void;
    public abstract function getEssayTextZoom(): float;
    public abstract function setEssayTextZoom(float $essay_text_zoom): void;
    public abstract function getSummaryTextZoom(): float;
    public abstract function setSummaryTextZoom(float $summary_text_zoom): void;
    public abstract function getIncludeComments(): int;
    public abstract function setIncludeComments(int $include_comments): void;
    public abstract function getIncludeCommentRatings(): int;
    public abstract function setIncludeCommentRatings(int $include_comment_ratings): void;
    public abstract function getIncludeCommentPoints(): int;
    public abstract function setIncludeCommentPoints(int $include_comment_points): void;
    public abstract function getIncludeCriteriaPoints(): int;
    public abstract function setIncludeCriteriaPoints(int $include_criteria_points): void;
}
