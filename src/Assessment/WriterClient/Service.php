<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WriterClient;

use Edutiek\AssessmentService\Assessment\Api\ApiException;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\WriterClient;
use Edutiek\AssessmentService\Assessment\Data\TokenPurpose;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\System\Entity\Service as EntityService;
use Edutiek\AssessmentService\Assessment\Data\Token;

class Service implements ReadService, FullService
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private Repositories $repos,
        private EntityService $entities
    ) {
    }

    public function all(int $writer_id)
    {
        $this->checkScope($writer_id);
        return $this->repos->writerClient()->allByWriterId($writer_id);
    }

    public function create(Writer $writer): WriterClient
    {
        $token = $this->currentToken($writer);
        if ($token === null) {
            throw new ApiException("token missing for the writer", ApiException::ID_SCOPE);
        }

        // reuse a client if found with current PHP session
        $client = $this->repos->writerClient()->oneByWriterIdAndSessionId($writer->getId(), session_id());

        if ($client === null) {
            $client = $this->repos->writerClient()->new()
                        ->setWriterId($writer->getId())
                        ->setSessionId(session_id())
                        ->setFirstAccess(new \DateTimeImmutable('now'));
        } else {
            // delete the old record because token has changed
            // the following save will write it with the new token id
            $this->repos->writerClient()->deleteByWriterIdAndTokenId($client->getWriterId(), $client->getTokenId());
        }

        $this->save(
            $client
            ->setTokenId($token->getId())
            ->setLastAccess(new \DateTimeImmutable('now'))
            ->setIp($_SERVER['REMOTE_ADDR'])
            ->setUserAgent($_SERVER['HTTP_USER_AGENT'])
        );
        return $client;
    }

    public function current(Writer $writer): ?WriterClient
    {
        $token = $this->currentToken($writer);
        if ($token !== null) {
            return $this->repos->writerClient()->oneByWriterIdAndTokenId($writer->getId(), $token->getId());
        }
        return null;
    }

    public function save(WriterClient $client): void
    {
        $this->checkScope($client->getWriterId());
        $client = $this->entities->secure($client, WriterClient::class);
        $this->repos->writerClient()->save($client);
    }

    /**
     * The token is needed to identify the client record in REST calls (session not available)
     */
    private function currentToken(Writer $writer): ?Token
    {
        return $this->repos->token()->oneByIdsAndPurpose($writer->getUserId(), $writer->getAssId(), TokenPurpose::DATA);
    }

    private function checkScope(int $writer_id)
    {
        if (!$this->repos->writer()->hasByWriterIdAndAssId($writer_id, $this->ass_id)) {
            throw new ApiException("wrong writer_id", ApiException::ID_SCOPE);
        }
    }
}
