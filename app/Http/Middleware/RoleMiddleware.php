<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $allowedRoles = explode('|', $roles);
        $userRole = $request->user()->akses;

        // Check if user has required access level
        if (!in_array($userRole, $allowedRoles)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}

