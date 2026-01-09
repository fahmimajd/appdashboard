<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // Check session flag
            if (session('force_password_change')) {
                $routeName = $request->route()->getName();
                
                $allowedRoutes = [
                    'password.change',
                    'password.update',
                    'logout',
                ];
                
                if (!in_array($routeName, $allowedRoutes)) {
                    return redirect()->route('password.change')
                        ->with('warning', 'Anda login menggunakan password manual. Wajib ganti password sekarang.');
                }
            }
        }

        return $next($request);
    }
}
