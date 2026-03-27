<?php

namespace App\Events;

use App\Models\Ticket;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SlaWarningTriggered implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public string $target,
        public int $thresholdPercentage,
        public CarbonImmutable $triggeredAt,
    ) {
    }
}
