<?php

namespace App\Services;

use App\Models\RencanaPembelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OnlyOfficeCallbackService
{
    public function handleTempCallback(Request $request, string $tempPath): array
    {
        return $this->handle($request, $tempPath, null);
    }

    public function handleCallback(Request $request, RencanaPembelajaran $rencanaPembelajaran): array
    {
        return $this->handle($request, null, $rencanaPembelajaran);
    }

    protected function handle(Request $request, ?string $tempPath, ?RencanaPembelajaran $rencanaPembelajaran): array
    {
        $payload = $this->normalizeRequest($request);
        $status = $this->normalizeStatus($payload['status'] ?? null);
        $fileUrl = $payload['url'] ?? null;
        $jwt = $payload['token'] ?? null;

        Log::info('OnlyOffice callback received', [
            'path' => $request->path(),
            'method' => $request->method(),
            'query' => $request->query(),
            'headers' => $this->normalizeHeaders($request->headers->all()),
            'payload' => $payload,
            'tempPath' => $tempPath,
            'rencanaPembelajaranId' => $rencanaPembelajaran?->id,
        ]);

        if ($status === null) {
            Log::warning('OnlyOffice callback missing status', ['payload' => $payload]);
            return $this->successResponse();
        }

        if (!in_array($status, [1, 2, 3, 4, 6, 7], true)) {
            Log::warning('OnlyOffice callback unsupported status', ['status' => $status, 'payload' => $payload]);
            return $this->successResponse();
        }

        switch ($status) {
            case 1:
                Log::info('OnlyOffice callback status 1: document opened');
                return $this->successResponse();

            case 4:
                Log::info('OnlyOffice callback status 4: document closed without changes');
                return $this->successResponse();

            case 3:
                Log::warning('OnlyOffice callback status 3: document error', ['payload' => $payload]);
                return $this->successResponse();

            case 7:
                Log::warning('OnlyOffice callback status 7: force save failed', ['payload' => $payload]);
                return $this->successResponse();

            case 2:
            case 6:
                return $this->handleSaveStatus($fileUrl, $tempPath, $rencanaPembelajaran, $payload);
        }

        return $this->successResponse();
    }

    protected function handleSaveStatus(?string $fileUrl, ?string $tempPath, ?RencanaPembelajaran $rencanaPembelajaran, array $payload): array
    {
        if (empty($fileUrl)) {
            Log::error('OnlyOffice callback save status missing file URL', ['payload' => $payload]);
            return $this->successResponse();
        }

        try {
            $contents = $this->downloadFile($fileUrl);

            if ($tempPath !== null) {
                $publicTempFile = public_path('uploads/' . $tempPath);
                $this->ensureDirectoryExists(dirname($publicTempFile));
                File::put($publicTempFile, $contents);
                Log::info('OnlyOffice temp document saved', ['publicTempFile' => $publicTempFile, 'size' => strlen($contents)]);
            }

            if ($rencanaPembelajaran !== null && !empty($rencanaPembelajaran->original_docx_path)) {
                $publicFile = public_path($rencanaPembelajaran->original_docx_path);
                $this->ensureDirectoryExists(dirname($publicFile));
                File::put($publicFile, $contents);
                Log::info('OnlyOffice document saved to public path', [
                    'publicFile' => $publicFile,
                    'original_docx_path' => $rencanaPembelajaran->original_docx_path,
                    'size' => strlen($contents),
                ]);

                $this->refreshRencanaHtml($rencanaPembelajaran, $contents);
            }

            return $this->successResponse();
        } catch (\Throwable $e) {
            Log::error('OnlyOffice callback save error', [
                'message' => $e->getMessage(),
                'fileUrl' => $fileUrl,
                'payload' => $payload,
            ]);

            return $this->successResponse();
        }
    }

    protected function downloadFile(string $url): string
    {
        $response = Http::timeout(120)->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException(sprintf('OnlyOffice file download failed: %s', $response->status()));
        }

        return $response->body();
    }

    protected function refreshRencanaHtml(RencanaPembelajaran $rencanaPembelajaran, string $contents): void
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'onlyoffice_');
        File::put($tempPath, $contents);

        try {
            $updatedHtml = app()->call([\App\Http\Controllers\RencanaPembelajaranController::class, 'convertDocxToHtml'], ['path' => $tempPath]);
            $rencanaPembelajaran->html_content = $updatedHtml;
            $rencanaPembelajaran->save();
            Log::info('OnlyOffice callback HTML refreshed', ['rencanaPembelajaranId' => $rencanaPembelajaran->id]);
        } finally {
            @unlink($tempPath);
        }
    }

    protected function normalizeRequest(Request $request): array
    {
        $body = $request->all();
        if (trim($request->getContent()) !== '') {
            $json = json_decode($request->getContent(), true);
            if (is_array($json)) {
                $body = array_merge($body, $json);
            }
        }

        return $body;
    }

    protected function normalizeStatus($status): ?int
    {
        if ($status === null) {
            return null;
        }

        if (is_int($status)) {
            return $status;
        }

        if (is_string($status) && preg_match('/^\d+$/', $status)) {
            return (int) $status;
        }

        return null;
    }

    protected function normalizeHeaders(array $headers): array
    {
        return collect($headers)->mapWithKeys(function ($value, $key) {
            return [$key => is_array($value) ? implode(',', $value) : $value];
        })->toArray();
    }

    protected function ensureDirectoryExists(string $directory): void
    {
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function successResponse(): array
    {
        return ['error' => 0];
    }
}
