<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileServeController extends Controller
{
    public function serve($path)
    {
        // Sanitize path to prevent directory traversal
        $path = preg_replace('/\.\./', '', $path);
        $path = ltrim($path, '/');
        
        // Check if file exists in public storage
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }
        
        // Get file info
        $file_path = storage_path('app/public/' . $path);
        $mime_type = mime_content_type($file_path);
        
        // Return file with proper headers
        return response()->file($file_path, [
            'Content-Type' => $mime_type,
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
