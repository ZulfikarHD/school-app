<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    /**
     * Display list of activity logs dengan advanced filtering, pagination,
     * dan optimization untuk security monitoring dan audit trail
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user:id,name,username,role');

        // Filter by date range (default: last 7 days)
        $dateFrom = $request->input('date_from', now()->subDays(7)->startOfDay()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->endOfDay()->format('Y-m-d'));

        if ($dateFrom && $dateTo) {
            $query->whereBetween('created_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59']);
        }

        // Filter by user
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        // Filter by action (support multiple actions)
        if ($actions = $request->input('actions')) {
            if (is_array($actions) && count($actions) > 0) {
                $query->whereIn('action', $actions);
            }
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Search by IP address or identifier
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                    ->orWhereRaw('LOWER(new_values) LIKE ?', ['%'.strtolower($search).'%']);
            });
        }

        // Order by created_at desc (newest first) dengan optimization
        $logs = $query->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        // Get available users untuk filter dropdown
        $users = User::select('id', 'name', 'username', 'role')
            ->orderBy('name')
            ->get();

        // Get available actions untuk filter dropdown
        $availableActions = ActivityLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return Inertia::render('Admin/AuditLogs/Index', [
            'logs' => $logs,
            'users' => $users,
            'availableActions' => $availableActions,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'user_id' => $request->input('user_id'),
                'actions' => $request->input('actions', []),
                'status' => $request->input('status'),
                'search' => $request->input('search'),
            ],
        ]);
    }
}
