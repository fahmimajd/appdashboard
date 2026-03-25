<?php

namespace App\Http\Controllers;

use App\Models\AdminActivityLog;
use Illuminate\Http\Request;

class AdminActivityLogController extends Controller
{
    /**
     * Display activity logs with filters
     */
    public function index(Request $request)
    {
        $query = AdminActivityLog::with('user')
            ->orderBy('created_at', 'desc');

        // Filter by module
        if ($request->filled('module')) {
            $query->byModule($request->module);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->byAction($request->action);
        }

        // Filter by user name search
        if ($request->filled('search')) {
            $query->where('user_name', 'like', '%' . $request->search . '%');
        }

        // Filter by date range
        if ($request->filled('date_from') || $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        $logs = $query->paginate(25)->appends($request->query());

        $modules = AdminActivityLog::$moduleLabels;
        $actions = AdminActivityLog::$actionLabels;

        return view('admin_activity_log.index', compact('logs', 'modules', 'actions'));
    }
}
