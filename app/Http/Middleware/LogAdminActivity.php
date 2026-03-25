<?php

namespace App\Http\Middleware;

use App\Models\AdminActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAdminActivity
{
    /**
     * Routes/methods to skip logging (noise reduction)
     */
    protected array $skipRoutes = [
        'admin-log.index',    // Don't log viewing the log page itself
        'dashboard',
        'api.stats.dashboard',
        'api.kecamatan',
        'api.desa',
        'api.kinerja.chart',
        'api.kinerja.prev-total-ikd',
        'password.change',    // Show form only
    ];

    /**
     * Map route name prefix → module
     */
    protected array $moduleMap = [
        'kinerja.' => 'kinerja',
        'kinerja-kecamatan.' => 'kinerja-kecamatan',
        'belum_rekam.' => 'belum-rekam',
        'belum_akte.' => 'belum-akte',
        'pendamping.' => 'pendamping',
        'users.' => 'user-management',
        'petugas.' => 'petugas',
        'management-barang.' => 'management-barang',
        'kependudukan.' => 'kependudukan',
        'vpn.' => 'vpn',
        'sarpras.' => 'sarpras',
        'sasaran.' => 'belum-rekam',
        'login' => 'auth',
        'logout' => 'auth',
        'register' => 'auth',
        'password.' => 'auth',
    ];

    /**
     * Map route name suffix → action
     */
    protected array $actionMap = [
        'store' => 'create',
        'update' => 'update',
        'destroy' => 'delete',
        'approve-field' => 'approve',
        'approve-all' => 'approve',
        'reject-field' => 'reject',
        'reject-all' => 'reject',
        'export' => 'export',
        'upload-dokumen' => 'upload',
        'reset-password' => 'reset-password',
        'toggle-status' => 'toggle-status',
        'login' => 'login',
        'logout' => 'logout',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated users
        if (!$request->user()) {
            return $response;
        }

        // Only log state-changing requests (POST, PUT, DELETE) + some specific GETs
        $method = $request->method();
        $routeName = $request->route()?->getName() ?? '';

        // Skip noise routes
        if (in_array($routeName, $this->skipRoutes)) {
            return $response;
        }

        // For GET requests, only log exports
        if ($method === 'GET') {
            if (!str_contains($routeName, 'export')) {
                return $response;
            }
        }

        // Only log successful responses (2xx or 3xx redirects)
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 400) {
            return $response;
        }

        try {
            $action = $this->resolveAction($routeName, $method);
            $module = $this->resolveModule($routeName);
            $description = $this->buildDescription($request, $action, $module, $routeName);

            AdminActivityLog::logActivity($request, $action, $module, $description);
        } catch (\Throwable $e) {
            // Silently fail — logging should never break the app
            \Log::warning('Failed to log admin activity: ' . $e->getMessage());
        }

        return $response;
    }

    /**
     * Resolve the action type from the route name
     */
    protected function resolveAction(string $routeName, string $method): string
    {
        // Check exact match first (e.g. 'login', 'logout')
        if (isset($this->actionMap[$routeName])) {
            return $this->actionMap[$routeName];
        }

        // Check suffix match
        $parts = explode('.', $routeName);
        $suffix = end($parts);

        if (isset($this->actionMap[$suffix])) {
            return $this->actionMap[$suffix];
        }

        // Fallback by HTTP method
        return match ($method) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'other',
        };
    }

    /**
     * Resolve the module from the route name
     */
    protected function resolveModule(string $routeName): string
    {
        foreach ($this->moduleMap as $prefix => $module) {
            if (str_starts_with($routeName, $prefix) || $routeName === $prefix) {
                return $module;
            }
        }

        return 'other';
    }

    /**
     * Build a human-readable description (Modul, NIK, Perubahan)
     */
    protected function buildDescription(Request $request, string $action, string $module, string $routeName): string
    {
        $user = $request->user();
        $nik = $user->nik ?? 'Unknown';
        $moduleLabel = AdminActivityLog::$moduleLabels[$module] ?? $module;
        $actionLabel = AdminActivityLog::$actionLabels[$action] ?? $action;

        // Base change description
        $perubahan = $actionLabel;

        // Add target identifier if available
        $routeParams = $request->route()?->parameters() ?? [];
        if (!empty($routeParams)) {
            $firstParam = array_values($routeParams)[0];
            $perubahan .= " Target ID: {$firstParam}";
        }

        return "Modul: {$moduleLabel}, NIK: {$nik}, Perubahan: {$perubahan}";
    }
}
