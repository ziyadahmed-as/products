<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use App\Models\Branch;
use Spatie\Permission\Models\Role;
use App\Services\ActivityLogger;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class AuditTrailController extends Controller
{
    /**
     * Display a comprehensive listing of system activity logs with multi-factor filtering.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Auditor')),
            403,
            'Unauthorized access. Only Super Admins and Auditors can view system activity reports.'
        );

        $query = Activity::with('causer')->latest('id');

        $query = $this->applyFilters($query, $request);

        // Calculate summary metrics on filtered dataset
        $totalActivities = (clone $query)->count();
        $successfulCount = (clone $query)->where(function ($q) {
            $q->whereNull('properties->outcome')
              ->orWhere('properties->outcome', 'success');
        })->count();
        $failedCount = (clone $query)->where('properties->outcome', 'failed')->count();
        $warningCount = (clone $query)->where('properties->outcome', 'warning')->count();

        $activities = $query->paginate(25)->withQueryString();

        // Filter options for the UI
        $filterUsers = User::select('id', 'name', 'email')->orderBy('name')->get();
        $filterRoles = Role::pluck('name', 'name')->toArray();
        $filterBranches = Branch::where('is_active', true)->pluck('name', 'id')->toArray();
        $filterModules = [
            'Auth'          => 'Authentication & Security',
            'Sales'         => 'Sales & Orders',
            'Inventory'     => 'Inventory & Stock',
            'Manufacturing' => 'Manufacturing & Production',
            'Users'         => 'User Management',
            'Transfers'     => 'Stock Transfers',
            'Reviews'       => 'Customer Reviews',
            'Reports'       => 'Reports & Exports',
        ];
        $filterActionTypes = [
            'login'          => 'Login',
            'logout'         => 'Logout',
            'failed_login'   => 'Failed Login',
            'register'       => 'Registration',
            'create'         => 'Create',
            'update'         => 'Update',
            'delete'         => 'Delete',
            'approve'        => 'Approval',
            'complete'       => 'Completion',
            'cancel'         => 'Cancellation',
            'issue_materials'=> 'Material Issue',
            'stock_deducted' => 'Stock Deducted',
            'payment'        => 'Payment Received',
            'export'         => 'Export',
        ];

        return view('reports.audit', compact(
            'activities',
            'totalActivities',
            'successfulCount',
            'failedCount',
            'warningCount',
            'filterUsers',
            'filterRoles',
            'filterBranches',
            'filterModules',
            'filterActionTypes'
        ));
    }

    /**
     * Export the filtered activity log to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->hasRole('Super Admin') || $user->hasRole('Auditor')),
            403,
            'Unauthorized access.'
        );

        ActivityLogger::log(
            module: 'Reports',
            actionType: 'export',
            description: "User {$user->name} exported User Activity Logs to CSV.",
            properties: ['format' => 'CSV']
        );

        $query = Activity::with('causer')->latest('id');
        $query = $this->applyFilters($query, $request);

        $filename = 'user_activity_report_' . date('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Header Row
            fputcsv($handle, [
                'ID',
                'Date & Time',
                'User',
                'Email',
                'Role',
                'Module',
                'Action Type',
                'Description',
                'Reference #',
                'Branch',
                'Outcome',
                'IP Address',
                'User Agent',
            ]);

            $query->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $act) {
                    $props = is_array($act->properties) ? $act->properties : (json_decode($act->properties, true) ?? []);
                    fputcsv($handle, [
                        $act->id,
                        $act->created_at ? $act->created_at->format('Y-m-d H:i:s') : '',
                        $act->causer?->name ?? ($props['username'] ?? 'System'),
                        $act->causer?->email ?? ($props['user_email'] ?? ''),
                        $props['role'] ?? '',
                        $act->log_name ?? '',
                        $act->event ?? ($props['action_type'] ?? ''),
                        $act->description,
                        $props['reference'] ?? '',
                        $props['branch_name'] ?? '',
                        $props['outcome'] ?? 'success',
                        $props['ip'] ?? '',
                        $props['user_agent'] ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Apply request filters to activity query.
     */
    protected function applyFilters($query, Request $request)
    {
        // 1. Date Period Filter (today, this_week, this_month, this_year, custom)
        $period = $request->input('period');
        if ($period === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($period === 'this_week') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($period === 'this_month') {
            $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        } elseif ($period === 'this_year') {
            $query->whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]);
        } elseif ($period === 'custom' || $request->filled('from') || $request->filled('to')) {
            if ($request->filled('from')) {
                $query->where('created_at', '>=', Carbon::parse($request->from)->startOfDay());
            }
            if ($request->filled('to')) {
                $query->where('created_at', '<=', Carbon::parse($request->to)->endOfDay());
            }
        }

        // 2. User Filter
        if ($request->filled('user_id')) {
            $userId = (int) $request->user_id;
            $query->where(function ($q) use ($userId) {
                $q->where('causer_id', $userId)
                  ->orWhere('properties->user_id', $userId);
            });
        }

        // 3. Role Filter
        if ($request->filled('role')) {
            $query->where('properties->role', $request->role);
        }

        // 4. Branch Filter
        if ($request->filled('branch_id')) {
            $branchId = (int) $request->branch_id;
            $query->where('properties->branch_id', $branchId);
        }

        // 5. Module Filter
        if ($request->filled('module')) {
            $query->where('log_name', $request->module);
        }

        // 6. Action Type Filter
        if ($request->filled('action_type')) {
            $action = $request->action_type;
            $query->where(function ($q) use ($action) {
                $q->where('event', $action)
                  ->orWhere('properties->action_type', $action);
            });
        }

        // 7. Status / Outcome Filter
        if ($request->filled('outcome')) {
            $query->where('properties->outcome', $request->outcome);
        }

        // 8. Text Search Filter (Description, reference, username, email)
        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', $term)
                  ->orWhere('properties->reference', 'like', $term)
                  ->orWhere('properties->username', 'like', $term)
                  ->orWhere('properties->user_email', 'like', $term);
            });
        }

        return $query;
    }
}
