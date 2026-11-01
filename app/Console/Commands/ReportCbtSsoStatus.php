<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use App\Models\CbtSsoTicket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReportCbtSsoStatus extends Command
{
    protected $signature = 'sso:integration-status';

    protected $description = 'Report aggregate CBT SSO integration health without personal data';

    public function handle(): int
    {
        $dayAgo = now()->subDay();
        $status = [
            'cbt_sso_start_url_configured' => filled(config('services.cbt_sso.start_url')),
            'cbt_sso_callback_url_configured' => filled(config('services.cbt_sso.callback_url')),
            'active_api_keys' => ApiKey::query()->whereNull('revoked_at')->count(),
            'api_keys_used_last_24h' => ApiKey::query()
                ->whereNull('revoked_at')
                ->where('last_used_at', '>=', $dayAgo)
                ->count(),
            'tickets_issued_last_24h' => CbtSsoTicket::query()
                ->where('created_at', '>=', $dayAgo)
                ->count(),
            'tickets_consumed_last_24h' => CbtSsoTicket::query()
                ->whereNotNull('consumed_at')
                ->where('consumed_at', '>=', $dayAgo)
                ->count(),
            'expired_unconsumed_tickets' => CbtSsoTicket::query()
                ->whereNull('consumed_at')
                ->where('expires_at', '<=', now())
                ->count(),
            'tickets_past_retention' => CbtSsoTicket::query()
                ->whereNull('consumed_at')
                ->where('expires_at', '<', $dayAgo)
                ->count(),
        ];

        $attentionRequired = ! $status['cbt_sso_start_url_configured']
            || ! $status['cbt_sso_callback_url_configured']
            || $status['active_api_keys'] === 0
            || $status['tickets_past_retention'] > 0;
        $status['status'] = $attentionRequired ? 'attention_required' : 'ready_for_review';

        if ($attentionRequired) {
            Log::error('CBT SSO operational status requires attention.', $status);
        } else {
            Log::info('CBT SSO operational status report.', $status);
        }
        $this->table(
            ['Metric', 'Value'],
            collect($status)->map(fn ($value, $metric) => [
                $metric,
                is_bool($value) ? ($value ? 'yes' : 'no') : $value,
            ])->all()
        );

        return $attentionRequired ? self::FAILURE : self::SUCCESS;
    }
}
