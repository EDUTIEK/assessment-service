<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\EssayImport;

use Edutiek\AssessmentService\System\File\ReadOnlyStorage;
use Edutiek\AssessmentService\Task\Manager\ReadService as TaskReadService;
use Edutiek\AssessmentService\EssayTask\Essay\ClientService as EssayService;
use Edutiek\AssessmentService\Assessment\Writer\FullService as WriterService;
use Edutiek\AssessmentService\System\User\ReadService as UserService;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskInfo as Task;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use DateTimeImmutable;

class Service implements Problems, FullService
{
    private array $cache = [];

    /**
     * @param array<string, Type> $available_types
     */
    public function __construct(
        private readonly ReadOnlyStorage $storage,
        private readonly WriterService $writer_service,
        private readonly TaskReadService $task_service,
        private readonly EssayService $essay_service,
        private readonly UserService $user_service,
        private readonly int $user_id,
        private readonly Import $import,
        private readonly array $available_types,
    ){}

    public function type(string $type): Type
    {
        return $this->available_types[$type]($this);
    }

    public function import(Type $type, array $file_map, bool $overwrite_existing = false): int
    {
        $pdfs = $type->validFilesByLogin(array_keys($file_map));
        $users = $this->loginsByUserId(array_keys($pdfs));
        $now = new DateTimeImmutable();
        $imported = 0;

        foreach ($users as $user_id => $login) {
            $zip_pdf = $file_map[$pdfs[$login] ?? null] ?? null;
            if (!$zip_pdf) {
                continue;
            }

            $writer = $this->writer_service->getByUserId($user_id);
            $task = $this->task();
            $essay = $this->essayByWriter($writer->getId()) ??
                $this->essay_service->new($writer->getId(), $task->getId())->setFirstChange($now);
            $essay = $essay->setLastChange($now);
            $pdf = $essay->getPdfVersion();
            $file_exists = false;
            if ($pdf) {
                $file_exists = $this->storage->getFileStream($pdf);
                $file_exists && fclose($file_exists);
                $file_exists = (bool) $file_exists;
            }
            if ($file_exists && !$overwrite_existing) {
                continue;
            }
            $zip_pdf = $this->import->permanentId($zip_pdf);
            $essay->setPdfVersion($zip_pdf);
            $this->essay_service->replacePdf($essay, $zip_pdf);
            $this->writer_service->authorizeWriting($writer, $this->user_id, true);
            $imported++;
        }

        return $imported;
    }

    public function isRelevantFile(string $name): bool
    {
        return [] !== array_filter($this->available_types, fn($create) => [] !== $create($this)->relevantFiles([$name]));
    }

    public function typeByFiles(array $files): ?string
    {
        foreach ($this->available_types as $key => $create) {
            if ([] !== $create($this)->relevantFiles($files)) {
                return $key;
            }
        }

        return null;
    }

    public function problems(string $login, array $pdfs, array $hashes): array
    {
        $ret = ['errors' => [], 'overwrites' => []];
        if (!isset($pdfs[$login])) {
            $ret['errors'][] = $this->import->txt('import_file_missing');
        }

        $user_id = $this->user_service->getUserIdByLogin($login);
        if (!$user_id) {
            $ret['errors'][] = $this->import->txt('import_user_not_existing');
            return $ret;
        }

        $writer = $this->writerByUser($user_id);
        if (!$writer) {
            return $ret;
        }
        $task = $this->task();
        $essay = $this->essayByWriter($writer->getId());
        if (!$essay) {
            return $ret;
        }
        $pdf = $essay->getPdfVersion();
        if (!$pdf) {
            return $ret;
        }
        $stream = $this->storage->getFileStream($pdf);
        if (!$stream) {
            return $ret;
        }

        $same = $hashes[$pdfs[$login]] === $this->import->hash(stream_get_contents($stream));
        fclose($stream);
        $ret['overwrites'][] = $same ?
            $this->import->txt('import_same_file_exists') :
            $this->import->txt('import_another_file_exists');

        return $ret;
    }

    private function writerByUser(int $user_id): ?Writer
    {
        $this->cache['writers'] ??= $this->import->keysBy(
            fn(Writer $writer) => $writer->getUserId(),
            $this->writer_service->all()
        );

        return $this->cache['writers'][$user_id] ?? null;
    }

    private function task(): Task
    {
        return $this->cache['task'] ??= $this->task_service->first();
    }

    private function essayByWriter(int $writer_id): ?Essay
    {
        $this->cache['essays'] ??= $this->import->keysBy(
            fn(Essay $essay) => $essay->getWriterId(),
            $this->essay_service->allByTaskId($this->task()->getId())
        );

        return $this->cache['essays'][$writer_id] ?? null;
    }

    /**
     * @return array<int, string>
     */
    private function loginsByUserId(array $logins): array
    {
        $users = $this->import->keysBy($this->user_service->getUserIdByLogin(...), $logins);
        unset($users[0]); // Remove not found users.

        return $users;
    }
}
