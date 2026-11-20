<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Mail;

interface Delivery
{
    /**
     * Deliver a mail to users given by ids
     * @param int[] $to_ids
     * @param int[] $cc_ids
     * @param int[] $bc_ids
     */
    public function internal(string $subject, string $body, array $to_ids, array $cc_ids = [], array $bc_ids = []): void;

    /**
     * Deliver a mail to external addresses
     * @param string[] $to
     * @param string[] $cc
     * @param string[] $bc
     */
    public function external(string $subject, string $body, array $to, array $cc = [], array $bc = []): bool;
}
