<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves admin-managed 301/302 redirects (FR-35–FR-39).
 *
 * Registered globally so it can catch old URLs that no longer have a route
 * (a web-group middleware would be skipped on a 404). Admin routes are never
 * redirected (FR-38).
 */
class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethodSafe() || $request->is('admin', 'admin/*')) {
            return $next($request);
        }

        $path = ltrim($request->path(), '/');

        $redirect = Redirect::query()
            ->where('is_active', true)
            ->whereIn('from_url', array_unique(['/'.$path, $path]))
            ->first();

        if ($redirect) {
            return redirect($redirect->to_url, $redirect->type->value);
        }

        return $next($request);
    }
}
