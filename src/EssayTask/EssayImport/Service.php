<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\EssayImport;

use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\Writer\FullService as WriterService;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Essay\ClientService as EssayService;
use Edutiek\AssessmentService\System\Config\ReadService as SystemConfig;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\System\Log\FullService as SystemLog;
use Edutiek\AssessmentService\System\Session\Storage as SessionStorage;
use Edutiek\AssessmentService\System\User\ReadService as UserService;
use ZipArchive;

class Service implements FullService
{
    private array $cache = [];

    /**
     * @param ImportType[] $types indexed by class name
     */
    public function __construct(
        private readonly int $task_id,
        private readonly Storage $perm_store,
        private readonly Storage $temp_store,
        private readonly SessionStorage $session,
        private readonly SystemConfig $sys_config,
        private readonly SystemLog $sys_log,
        private readonly UserService $user_service,
        private readonly WriterService $writer_service,
        private readonly EssayService $essay_service,
        private readonly Language $lng,
        private readonly array $types,
    ) {
    }

    /**
     * Process an uploaded zip file
     */
    public function processZipFile(string $temp_file_id, ?string $password, ?string $required_hash): ImportResult
    {
        $this->session->set('type', null);
        $this->session->set('files', null);

        $result = new ImportResult();
        $zip = new ZipArchive();

        $stream = $this->temp_store->getFileStream($temp_file_id);
        if (!$stream) {
            return $result->add(false, $this->lng->txt('import_file_missing'));
        }

        $content = stream_get_contents($stream);
        $path = stream_get_meta_data($stream)['uri'];

        if ($required_hash && $required_hash !== $this->hash($content)) {
            return $result->add(false, $this->lng->txt('import_hash_mismatch'));
        }

        $code = $zip->open($path, ZipArchive::RDONLY);
        switch ($code) {
            case true:
                break;
            case ZipArchive::ER_NOZIP:
                return $result->add(false, $this->lng->txt('import_not_a_zip'));
            case ZipArchive::ER_INCONS:
                return $result->add(false, $this->lng->txt('import_zip_inconsistent'));
            default:
                $this->sys_log->error('Opening the ZIP file failed with code ' . $code);
                return $result->add(false, $this->lng->txt('import_unknown_error'));
        }

        if ($password !== null) {
            $zip->setPassword($password);
        }

        if ($zip->numFiles == 0) {
            return $result->add(false, $this->lng->txt('import_zip_empty'));
        }

        $filenames = [];
        for ($i = 0; $i < $zip->numFiles - 1; $i++) {
            $stat = $zip->statIndex($i);
            if (($stat['encryption_method'] ?? false) && $password === null) {
                return $result->add(false, $this->lng->txt('import_no_passwort_given'));
            }
            $filenames[] = $stat['name'];
        }

        $import_type = null;
        foreach ($this->types as $type) {
            if ($type->detectByFilenames($filenames)) {
                $import_type = $type;
            }
        }

        if ($import_type === null) {
            return $result->add(false, $this->lng->txt('import_invalid_zip_format'));
        }

        $files = [];
        foreach ($filenames as $name) {
            $stream = $zip->getStream($name);
            $content = stream_get_contents($stream);
            $info = $this->temp_store->saveFile($stream, null);
            fclose($stream);

            $files[] = (new ImportFile())
                ->setTempId($info->getId())
                ->setFileName(($name))
                ->setMimeType($info->getMimeType())
                ->setHash($this->hash($content));
        }

        $result = $import_type->assignFiles($files);
        if ($result->isOk()) {
            $this->checkFiles($files);
            $this->session->set('type', $import_type::class);
            $this->session->set('files', $files);
        }

        return $result;
    }

    /**
     * Check the relevant files
     * - assign the user id
     * - check of a pdf file already exists for the essay
     * @param ImportFile[] $files
     */
    private function checkFiles(array $files): void
    {
        foreach ($files as $file) {
            if (!$file->isRelevant()) {
                break;
            }

            $user_id = $this->user_service->getUserIdByLogin($file->getLogin());
            if ($user_id) {
                $file->setUserId($user_id);
            } else {
                $file->addError($this->lng->txt('import_user_not_existing'));
                break;
            }

            $pdf = $this->essayByWriter($this->writerByUser($user_id)?->getId())?->getPdfVersion();
            if ($pdf) {
                $stream = $this->perm_store->getFileStream($pdf);
                $same = $this->hash(stream_get_contents($stream)) === $file->getHash();
                $file->setExisting(true)
                    ->addComment($same ?
                        $this->lng->txt('import_same_file_exists') :
                        $this->lng->txt('import_another_file_exists'));
            }
        }
    }

    public function relevantFiles(): array
    {
        $files = $this->session->get('files') ?? [];
        return array_filter($files, fn($file) => $file->isRelevant());
    }

    public function tableColumns(): array
    {
        $type = $this->types[$this->session->get('type') ?? ''] ?? null;
        return $type?->columns() ?? [];
    }

    public function tableRows(): array
    {
        $type = $this->types[$this->session->get('type') ?? ''] ?? null;
        $files = $this->session->get('files') ?? [];
        return $type?->rows($files) ?? [];
    }

    public function importFiles($overwrite_existing = false): int
    {
        /** @var ImportFile[] $files */
        $files = $this->session->get('files') ?? [];

        $imported = 0;
        foreach ($files as $file) {
            if ($file->isImportPossible()) {
                $writer = $this->writer_service->getByUserId($file->getUserId());
                $essay = $this->essay_service->getByWriterIdAndTaskId($writer->getId(), $this->task_id);
                if ($essay->getPdfVersion() === null || $overwrite_existing) {
                    $info = $this->perm_store->saveFile($this->temp_store->getFileStream($file->getTempId()));
                    $this->essay_service->replacePdf($essay, $info->getId());
                    $imported++;
                }
            }
        }

        $this->cleanup();
        return $imported;
    }


    public function cleanup(): void
    {
        $files = $this->session->get('files') ?? [];
        foreach ($files as $file) {
            $this->temp_store->deleteFile($file->getTempId());
        }
        $this->session->set('type', null);
        $this->session->set('files', null);
    }

    /**
     * Index an array with keys generated from a callable procedure on the array elements
     * @template A
     * @template B
     *
     * @param callable(A): B $proc
     * @param A[] $array
     * @return array<B, A>
     */
    private function keysBy(callable $proc, array $array): array
    {
        return array_column(array_map(
            fn($x) => ['value' => $x, 'key' => $proc($x)],
            $array
        ), 'value', 'key');
    }

    private function hash(string $value): string
    {
        return hash($this->sys_config->getConfig()->getHashAlgo(), $value);
    }

    private function writerByUser(int $user_id): ?Writer
    {
        $this->cache['writers'] ??= $this->keysBy(
            fn(Writer $writer) => $writer->getUserId(),
            $this->writer_service->all()
        );

        return $this->cache['writers'][$user_id] ?? null;
    }

    private function essayByWriter(int $writer_id): ?Essay
    {
        $this->cache['essays'] ??= $this->keysBy(
            fn(Essay $essay) => $essay->getWriterId(),
            $this->essay_service->allByTaskId($this->task_id)
        );

        return $this->cache['essays'][$writer_id] ?? null;
    }

    /**
     * @return array<int, string>
     */
    private function loginsByUserId(array $logins): array
    {
        $users = $this->keysBy($this->user_service->getUserIdByLogin(...), $logins);
        unset($users[0]); // Remove not found users.
        return $users;
    }
}
