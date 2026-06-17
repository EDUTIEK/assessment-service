<?php

namespace Edutiek\AssessmentService\Assessment\FileUsage;

use Edutiek\AssessmentService\Assessment\Api\CronHandler;
use Edutiek\AssessmentService\Assessment\Api\Internal;
use Edutiek\AssessmentService\Assessment\Api\Dependencies;
use Edutiek\AssessmentService\System\Data\Result;
use Edutiek\AssessmentService\System\File\FileUsageFinder;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\Language\ReadService as Language;

readonly class FileCleanupHandler implements CronHandler
{
    /**
     * @param FileUsageFinder[] $finders
     */
    public function __construct(
        private Storage $file_storage,
        private Storage $temp_storage,
        private array $finders,
        private Language $language
    ) {
    }

    public function run(): Result
    {
        $stored_ids = array_merge(
            $this->temp_storage->dayOldFileIds(),
            $this->file_storage->dayOldFileIds(),
        );

        $used_ids = [];
        foreach ($this->finders as $finder) {
            $used_ids = array_merge($used_ids, $finder->usedIds());
        }

        $deleted = 0;
        foreach (array_diff($stored_ids, $used_ids) as $id) {
            $this->file_storage->deleteFile($id);
            $deleted++;
        }

        return new Result(true, $this->language->txt('file_cleanup_handler_success', ['deleted' => $deleted]));
    }
}
