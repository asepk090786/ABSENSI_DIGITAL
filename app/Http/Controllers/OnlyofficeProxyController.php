<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class OnlyofficeProxyController extends Controller
{
    public function proxy(Request $request, string $path = '')
    {
        $onlyOfficeServerUrl = config('services.onlyoffice.server_url', env('ONLYOFFICE_SERVER_URL', env('ONLYOFFICE_URL')));
        $onlyOfficeServerUrl = trim((string) $onlyOfficeServerUrl);

        if ($onlyOfficeServerUrl === '') {
            abort(503, 'OnlyOffice server not configured.');
        }

        $onlyOfficeServerUrl = rtrim($onlyOfficeServerUrl, '/');
        $targetUrl = $onlyOfficeServerUrl . '/' . ltrim($path, '/');
        if ($query = $request->getQueryString()) {
            $targetUrl .= '?' . $query;
        }

        $headers = collect($request->headers->all())
            ->except(['host', 'content-length', 'x-csrf-token', 'x-xsrf-token'])
            ->mapWithKeys(function ($value, $key) {
                return [$key => implode(', ', $value)];
            })
            ->toArray();

        $method = $request->method();
        $response = Http::withHeaders($headers)
            ->withOptions(['verify' => false])
            ->send($method, $targetUrl, ['body' => $request->getContent()]);

        $responseHeaders = collect($response->headers())
            ->except(['transfer-encoding', 'content-encoding', 'connection'])
            ->mapWithKeys(function ($value, $key) {
                return [$key => implode(', ', $value)];
            })
            ->toArray();

        return response($response->body(), $response->status())
            ->withHeaders($responseHeaders);
    }
}
