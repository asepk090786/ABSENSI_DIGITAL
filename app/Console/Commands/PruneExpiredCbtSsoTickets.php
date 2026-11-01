<?php

namespace App\Console\Commands;

use App\Models\CbtSsoTicket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PruneExpiredCbtSsoTickets extends Command
{
    protected $signature = 'sso:prune-expired-tickets';

    protected $description = 'Remove CBT SSO tickets expired for more than 24 hours';

    public function handle(): int
    {
        $deleted = CbtSsoTicket::query()
            ->where('expires_at', '<', now()->subDay())
            ->delete();

        Log::info('Expired CBT SSO ticket cleanup completed.', [
            'deleted_count' => $deleted,
        ]);

        $this->info("Deleted {$deleted} expired CBT SSO ticket(s).");

        return self::SUCCESS;
    }
}
