<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class HttpMethodOverride
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('POST')) {
            // Check for _method field in POST data
            if ($request->has('_method')) {
                $method = strtoupper($request->input('_method'));
                
                // Only allow valid HTTP methods
                if (in_array($method, ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'])) {
                    $request->setMethod($method);
                }
            }
        }

        return $next($request);
    }
}
