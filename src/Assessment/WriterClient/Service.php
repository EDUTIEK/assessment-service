<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WriterClient;

use Edutiek\AssessmentService\Assessment\Api\ApiException;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\WriterClient;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\System\Entity\Service as EntityService;
use Edutiek\AssessmentService\System\Data\Token;
use Edutiek\AssessmentService\System\Rest\RestHelper;

class Service implements ReadService, FullService
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private Repositories $repos,
        private EntityService $entities,
        private RestHelper $rest
    ) {
    }

    public function all(int $writer_id)
    {
        $this->checkScope($writer_id);
        return $this->repos->writerClient()->allByWriterId($writer_id);
    }

    public function create(Writer $writer, Token $token): WriterClient
    {
        if ($writer->getUserId() !== $token->getUserId()) {
            throw new ApiException("token is for wrong user", ApiException::ID_SCOPE);
        }

        // reuse a client if found with current PHP session
        $client = $this->repos->writerClient()->oneByWriterIdAndSessionId($writer->getId(), session_id());

        if ($client === null) {

            // no client with current session id found
            // remove outdated data from the older clients
            // these should not be used in aggregate functions for the writer view
            foreach ($this->repos->writerClient()->allByWriterId($writer->getId()) as $old_client) {
                $this->repos->writerClient()->save(
                    $old_client
                        ->setSessionId(null)
                        ->setBattery(null)
                        ->setHidden(null)
                );
            }

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
        $token = $this->rest->getToken();
        // todo: check why token is not found
        //        if ($writer->getUserId() !== $token?->getUserId()) {
        //            throw new ApiException("token is for wrong user", ApiException::ID_SCOPE);
        //        }

        if ($token !== null) {
            return $this->repos->writerClient()->oneByWriterIdAndTokenId($writer->getId(), $token->getId());
        }
        return null;
    }

    public function save(WriterClient $client): void
    {
        $this->checkScope($client->getWriterId());
        $this->checkActive();
        $client = $this->entities->secure($client, WriterClient::class);
        $this->repos->writerClient()->save($client);
    }


    private function checkScope(int $writer_id): void
    {
        if (!$this->repos->writer()->hasByWriterIdAndAssId($writer_id, $this->ass_id)) {
            throw new ApiException("wrong writer_id", ApiException::ID_SCOPE);
        }
    }

    private function checkActive(): void
    {
        if (!$this->repos->orgaSettings()->one($this->ass_id)?->getDashboard()) {
            throw new ApiException("dashboard not active", ApiException::PRIVACY);
        }
    }
}
