<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');

        if (! $apiKey && preg_match('/^Bearer\s+(.+)$/i', (string) $request->header('Authorization'), $matches)) {
            $apiKey = $matches[1];
        }

        if (! is_string($apiKey) || trim($apiKey) === '') {
            return response()->json(['message' => 'API key diperlukan.'], 401);
        }

        $apiKey = trim($apiKey);
        $record = ApiKey::query()
            ->where('key_hash', hash('sha256', $apiKey))
            ->whereNull('revoked_at')
            ->first();

        if (! $record) {
            return response()->json(['message' => 'API key tidak valid atau sudah dicabut.'], 401);
        }

        $record->forceFill(['last_used_at' => now()])->saveQuietly();
        $request->attributes->set('api_key', $record);

        return $next($request);
    }
}
