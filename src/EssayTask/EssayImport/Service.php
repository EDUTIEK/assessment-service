<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\EssayImport;

use Edutiek\AssessmentService\EssayTask\Data\EssayImportRepo;
use Edutiek\AssessmentService\EssayTask\Data\EssayImport;

class Service implements FullService
{
    public function __construct(private readonly EssayImportRepo $repo)
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

    public function delete(int $id): void
    {
        $this->repo->delete($id);
    }
}
