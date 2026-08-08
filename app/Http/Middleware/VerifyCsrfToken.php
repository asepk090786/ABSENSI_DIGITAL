<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'rencana_pembelajaran/*/onlyoffice-callback',
        'rencana_pembelajaran/temp/*/onlyoffice-temp-callback',
        'modul-ajar/temp/*/onlyoffice-temp-callback',
        'public/modul-ajar/temp/*/document',
        'collabora/wopi/files/*/contents',
        'collabora/wopi/files/*/lock',
        'collabora/wopi/files/*/unlock',
        'collabora/wopi/files/*/refreshlock',
    ];
}
