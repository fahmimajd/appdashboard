<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class AdminActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'module',
        'description',
        'route_name',
        'http_method',
        'url',
        'ip_address',
        'user_agent',
        'request_data',
    ];

    protected $casts = [
        'request_data' => 'array',
    ];

    /**
     * Module labels in Indonesian
     */
    public static array $moduleLabels = [
        'kinerja' => 'Kinerja Petugas',
        'kinerja-kecamatan' => 'Kinerja Kecamatan',
        'belum-rekam' => 'Belum Rekam KTP-EL',
        'belum-akte' => 'Belum Akte Kelahiran',
        'pendamping' => 'Pendampingan',
        'user-management' => 'User Management',
        'petugas' => 'Petugas',
        'management-barang' => 'Manajemen Barang',
        'kependudukan' => 'Kependudukan',
        'vpn' => 'VPN',
        'sarpras' => 'Sarpras',
        'auth' => 'Autentikasi',
        'other' => 'Lainnya',
    ];

    /**
     * Action labels in Indonesian
     */
    public static array $actionLabels = [
        'login' => 'Login',
        'logout' => 'Logout',
        'create' => 'Tambah Data',
        'update' => 'Edit Data',
        'delete' => 'Hapus Data',
        'approve' => 'Approve',
        'reject' => 'Reject',
        'export' => 'Export',
        'upload' => 'Upload',
        'reset-password' => 'Reset Password',
        'toggle-status' => 'Ubah Status',
        'other' => 'Lainnya',
    ];

    /**
     * Relation to user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get module label
     */
    public function getModuleLabel(): string
    {
        return self::$moduleLabels[$this->module] ?? $this->module;
    }

    /**
     * Get action label
     */
    public function getActionLabel(): string
    {
        return self::$actionLabels[$this->action] ?? $this->action;
    }

    /**
     * Static helper to log an admin activity
     */
    public static function logActivity(
        Request $request,
        string $action,
        string $module,
        ?string $description = null
    ): self {
        $user = $request->user();

        // Filter sensitive data from request
        $requestData = $request->except([
            'password', 'password_confirmation', '_token', '_method',
        ]);

        return self::create([
            'user_id' => $user?->id,
            'user_name' => $user?->nama ?? 'Unknown',
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'route_name' => $request->route()?->getName(),
            'http_method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 255),
            'request_data' => !empty($requestData) ? $requestData : null,
        ]);
    }

    // — Scopes —

    public function scopeRecent($query, int $limit = 20)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeDateRange($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->where('created_at', '>=', $from . ' 00:00:00');
        }
        if ($to) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }
        return $query;
    }
}
