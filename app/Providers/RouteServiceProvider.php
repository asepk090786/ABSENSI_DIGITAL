<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Models\ApiKey;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            if ($request->is('api/cbt/students/*/photo')) {
                $apiKey = $request->header('X-API-Key');
                if (! $apiKey && preg_match('/^Bearer\s+(.+)$/i', (string) $request->header('Authorization'), $matches)) {
                    $apiKey = $matches[1];
                }

                if (is_string($apiKey) && trim($apiKey) !== '') {
                    $apiKeyId = ApiKey::query()
                        ->where('key_hash', hash('sha256', trim($apiKey)))
                        ->whereNull('revoked_at')
                        ->value('id');

                    if ($apiKeyId !== null) {
                        return Limit::perMinute(2000)->by('cbt-photo-api-key:' . $apiKeyId);
                    }
                }
            }

            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
