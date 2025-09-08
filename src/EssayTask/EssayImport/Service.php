<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\EssayImport;

use Edutiek\AssessmentService\EssayTask\Data\EssayImportRepo;
use Edutiek\AssessmentService\EssayTask\Data\EssayImport;
use Edutiek\AssessmentService\System\File\Storage;

class Service implements FullService
{
    public function __construct(
        private readonly EssayImportRepo $repo,
        private readonly Storage $file_storage,
    )
    {
    }

    public function new(string $file_id, ?string $password, ?string $hash): EssayImport
    {
        return $this->repo->new()
            ->setFileId($file_id)
            ->setPassword($password)
            ->setExpectedHash($hash);
    }

    public function getById(int $id): ?EssayImport
    {
        return $this->repo->one($id);
    }

    public function save(EssayImport $import): void
    {
        $this->repo->save($import);
    }

    public function delete(EssayImport $import): void
    {
        $this->repo->delete($import->getId());
        $this->file_storage->deleteFile($import->getFileId());
    }
}
