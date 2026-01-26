<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\WritingSteps;

use DiffMatchPatch\DiffMatchPatch;
use Edutiek\AssessmentService\EssayTask\Essay\ClientService as EssayService;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\System\Config\ReadService as ConfigService;
use Edutiek\AssessmentService\System\File\Storage as FileStorage;
use Edutiek\AssessmentService\System\Format\FullService as FormatService;
use Edutiek\AssessmentService\Task\Manager\ReadService as TasksReadService;
use Edutiek\AssessmentService\EssayTask\Data\WritingStep;
use ZipArchive;

class Service implements FullService
{
    public function __construct(
        private EssayService $essays,
        private TasksReadService $tasks,
        private Repositories $repos,
        private ConfigService $config,
        private FileStorage $storage,
        private FormatService $format,
    ) {
    }


    public function createExport(int $writer_id): string
    {
        $zipfile = $this->config->getSetup()->getAbsoluteTempPath()
            . uniqid('', true) . '.zip';

        $zip = new ZipArchive();
        $zip->open($zipfile, ZipArchive::CREATE);

        foreach ($this->tasks->all() as $task) {
            $task_dir = $this->storage->asciiFilename($task->getTitle());
            $zip->addEmptyDir($task_dir);

            $essay = $this->essays->oneByWriterIdAndTaskId($writer_id, $task->getId());
            if ($essay) {
                $before = '';
                $toc = '';
                $steps = $this->repos->writingStep()->allByEssayId($essay->getId());
                $index = 0;
                foreach ($steps as $step) {
                    $filename = 'step' . sprintf('%09d', $index) . '.html';

                    $nav = '<a href="index.html">Index</a> | Step ' . $index . ' (' . $step->getTimestamp()->getTimestamp() . ')';
                    if ($index > 0) {
                        $nav .= ' | <a href="step' . sprintf('%09d', $index - 1) . '.html">Previous</a>';
                    }
                    if ($index < count($steps) - 1) {
                        $nav .= ' | <a href="step' . sprintf('%09d', $index + 1) . '.html">Next</a>';
                    }

                    $toc .= '<a href="step' . sprintf('%09d', $index) . '.html">Step ' . $index . '</a> '
                        . ' (' . $this->format->date($step->getTimestamp()) . ')';

                    if ($step->getIsDelta()) {
                        $toc .= " - Incremental<br>\n";
                    } else {
                        $toc .= " - Full<br>\n";
                    }

                    $html = $nav . '<hr>' . $this->getWritingDiffHtml($before, $step);

                    $zip->addFromString($task_dir . '/' . $filename, $html);

                    $before = $this->getWritingDiffResult($before, $step);
                    $index++;
                }

                $zip->addFromString($task_dir . '/index.html', $toc);
            }
        }
        $zip->close();

        $fp = fopen($zipfile, 'r');
        $info = $this->storage->saveFile(
            $fp,
            $this->storage->newInfo()
            ->setFileName('writer' . $writer_id . '-steps.zip')
            ->setMimeType('application/zip')
        );
        unlink($zipfile);

        return $info->getId();
    }


    /**
     * Get the HTML diff of a writing step applied to a text
     */
    private function getWritingDiffHtml(string $before, WritingStep $step): string
    {
        $after = $this->getWritingDiffResult($before, $step);
        $dmp = new DiffMatchPatch();
        $diffs = $dmp->diff_main($before, $after);
        $dmp->diff_cleanupEfficiency($diffs);
        return $dmp->diff_prettyHtml($diffs);
    }

    /**
     * Get the result of a writing step
     */
    private function getWritingDiffResult(string $before, WritingStep $step): string
    {
        $dmp = new DiffMatchPatch();
        if ($step->getIsDelta()) {
            $patches = $dmp->patch_fromText($step->getContent());
            $result = $dmp->patch_apply($patches, $before);
            $after = $result[0];
        } else {
            $after = $step->getContent();
        }

        return $after;
    }
}
