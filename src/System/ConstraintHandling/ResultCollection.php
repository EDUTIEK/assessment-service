<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ConstraintHandling;

class ResultCollection
{
    /** @var array<string, Result[]> */
    private array $results = [
        ResultStatus::OK->value => [],
        ResultStatus::ASK->value => [],
        ResultStatus::BLOCK->value => [],
    ];

    /**
     * Add a result to the collection
     */
    public function add(Result $result): void
    {
        $this->results[$result->status()->value][] = $result;
    }

    /**
     * Get the overall result of the whole collection
     * If a BLOCK results exist then return a BLOCK result with merged messages of all BLOCK results
     * If an ASK results exist then return an ASK result with merged messages of all ASK results
     * Otherwise return an OK result without messages
     */
    public function result(): Result
    {
        foreach ([ResultStatus::BLOCK, ResultStatus::ASK] as $status) {
            $messages = [];
            if (!empty($this->results[$status->value])) {
                foreach ($this->results[$status->value] as $result) {
                    $messages = array_merge($messages, $result->messages());
                }
                return new Result($status, $messages);
            }
        }
        return new Result(ResultStatus::OK, []);
    }
}
