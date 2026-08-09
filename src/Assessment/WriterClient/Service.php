<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WriterClient;

use Edutiek\AssessmentService\Assessment\Api\ApiException;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\WriterClient;

class Service implements ReadService, FullService
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private Repositories $repos,
    ) {}

    public function all(int $writer_id)
    {
        $this->checkScope($writer_id);
        return $this->repos->writerClient()->allByWriterId($writer_id);
    }

    public function get(int $writer_id, int $token_id): WriterClient
    {
        $this->checkScope($writer_id);

        return $this->repos->writerClient()->oneByWriterIdAndTokenId($writer_id, $token_id)
            ?? $this->repos->writerClient()->new()
                ->setWriterId($writer_id)
                ->setTokenId($token_id)
                ->setFirstAccess(new \DateTimeImmutable('now'))
                ->setLastAccess(new \DateTimeImmutable('now'));
    }

    public function save(WriterClient $client) : void
    {
        $this->checkScope($client->getWriterId());
        $this->repos->writerClient()->save($client);
    }

    private function checkScope(int $writer_id)
    {
         if (!$this->repos->writer()->hasByWriterIdAndAssId($writer_id, $this->ass_id)) {
             throw new ApiException("wrong writer_id", ApiException::ID_SCOPE);
         }
    }
}