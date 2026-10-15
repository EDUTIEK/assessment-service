<?php

namespace Edutiek\AssessmentService\EssayTask\PersonalDataExport;

use Edutiek\AssessmentService\Assessment\PersonalDataExport\AbstractUsageResolver;
use Edutiek\AssessmentService\Assessment\PersonalDataExport\UsageScope;
use Edutiek\AssessmentService\Assessment\PersonalDataExport\UsageQuery;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\EssayTask\Data\Essay;

use function ILIAS\UI\examples\Input\Field\MultiSelect\empty_options;
use Edutiek\AssessmentService\Assessment\PersonalDataExport\UsageNode;
use Edutiek\AssessmentService\Assessment\PersonalDataExport\Group;

class EssayTaskUsageResolver extends AbstractUsageResolver
{
    public function __construct(
        private Repositories $repositories
    ) {

    }

    public function componentId(): string
    {
        return "EssayTask";
    }

    /**
     * @inheritDoc
     */
    public function resolveUsages(UsageQuery $query): array
    {
        $essays = $query->getWriterId() !== null
            ? $this->repositories->essay()->allByWriterId($query->getWriterId())
            : [];

        $essays = array_filter($essays, fn (Essay $e) => $e->getTaskId() === $query->getTaskId());
        $essay = !empty($essays) ? array_pop($essays) : null;

        $writer_notices = $essay !== null
            ? $this->repositories->writerNotice()->allByEssayId($essay->getId())
            : [];

        $writing_step = $essay !== null
            ? $this->repositories->writingStep()->allByEssayId($essay->getId())
            : [];

        $writer_prefs = $query->getWriterId() !== null
            ? $this->repositories->writerPrefs()->one($query->getWriterId())
            : null;

        if (
            $query->isUserIdUsedUpstream()
            || $essay !== null
            || count($writer_notices) > 0
            || count($writing_step) > 0
            || $writer_prefs !== null) {
            return [$this->createScope($query)];
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function collectData(UsageScope $scope, array $sub_nodes): \Edutiek\AssessmentService\Assessment\PersonalDataExport\UsageNode
    {
        $for_task = [];
        $for_assessment = [];

        if ($scope->isWriter()) {
            $assembly = new EssayGroupAssembly($scope);
            $essay = $this->repositories->essay()->oneByWriterIdAndTaskId($scope->getWriterId(), $scope->getTaskId());

            if ($essay !== null) {
                $assembly->addEssay($essay);
                $writer_notices = $this->repositories->writerNotice()->allByEssayId($essay->getId());
                if (!empty($writer_notices)) {
                    $assembly->addWriterNotices($writer_notices);
                }

                $writing_steps = $this->repositories->writingStep()->allByEssayId($essay->getId());
                if (!empty($writing_steps)) {
                    $assembly->addWritingSteps($writing_steps);
                }
                $for_task[] = $assembly->getGroup();
            }
        }

        if ($scope->isWriter()) {
            $prefs = $this->repositories->writerPrefs()->one($scope->getWriterId());
            if (!empty($prefs)) {
                $assembly = new WriterPrefsGroupAssembly($scope);
                $assembly->addWriterPrefs($prefs);
                $for_assessment[] = $assembly->getGroup();
            }
        }
        return $this->createNode($scope, [], $for_assessment, $for_task);

        //EssayImage W eid //maybe not needed with the original pdf
    }
}
