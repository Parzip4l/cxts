<?php

use App\Modules\Tickets\Tickets\TicketService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('tickets:sla-monitor {--limit=500}', function (TicketService $ticketService) {
    $summary = $ticketService->monitorSla((int) $this->option('limit'));

    $this->info(sprintf(
        'SLA monitor processed %d ticket(s) with limit %d.',
        $summary['processed'],
        $summary['limit'],
    ));
})->purpose('Evaluate SLA warnings and breaches for active tickets')->everyTenMinutes();
