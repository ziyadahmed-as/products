<?php

namespace App\Services;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ActivityLogger
{
    /**
     * Safely log an activity event to activity_log table without interrupting business logic.
     *
     * @param string $module         Module name: Auth, Sales, Inventory, Manufacturing, Users, Transfers, Reports, Reviews, System
     * @param string $actionType     Action type: login, logout, failed_login, create, update, delete, approve, complete, cancel, etc.
     * @param string $description    Human readable description
     * @param Model|null $subject    The Eloquent model subject (Sale, Product, User, etc.)
     * @param User|null $causer      The user performing the action (defaults to auth()->user())
     * @param array $properties      Additional structured metadata (before/after, branch_id, reference, etc.)
     * @param string $outcome        Outcome: success, failed, warning
     * @return Activity|null
     */
    public static function log(
        string $module,
        string $actionType,
        string $description,
        ?Model $subject = null,
        ?User $causer = null,
        array $properties = [],
        string $outcome = 'success'
    ): ?Activity {
        try {
            $user = $causer ?? (auth()->check() ? auth()->user() : null);
            $role = $user?->roles->first()?->name ?? ($user ? 'User' : 'System');

            // Resolve branch if available
            $branchId = $properties['branch_id'] ?? null;
            $branchName = $properties['branch_name'] ?? null;

            if (!$branchId && $subject) {
                if (isset($subject->branch_id) && $subject->branch_id) {
                    $branchId = $subject->branch_id;
                    $branchName = $subject->branch?->name ?? null;
                } elseif (isset($subject->storageLocation) && $subject->storageLocation?->branch_id) {
                    $branchId = $subject->storageLocation->branch_id;
                    $branchName = $subject->storageLocation->branch?->name ?? null;
                }
            }

            if (!$branchId && $user && method_exists($user, 'branches')) {
                $userBranch = $user->branches()->first();
                if ($userBranch) {
                    $branchId = $userBranch->id;
                    $branchName = $userBranch->name;
                }
            }

            // Resolve transaction reference
            $reference = $properties['reference'] ?? ($subject?->reference ?? null);

            // Clean sensitive data from properties
            $sanitizedProps = $properties;
            unset(
                $sanitizedProps['password'],
                $sanitizedProps['password_confirmation'],
                $sanitizedProps['token'],
                $sanitizedProps['remember_token'],
                $sanitizedProps['two_factor_secret'],
                $sanitizedProps['two_factor_recovery_codes']
            );

            if (isset($sanitizedProps['before']) && is_array($sanitizedProps['before'])) {
                unset(
                    $sanitizedProps['before']['password'],
                    $sanitizedProps['before']['remember_token']
                );
            }
            if (isset($sanitizedProps['after']) && is_array($sanitizedProps['after'])) {
                unset(
                    $sanitizedProps['after']['password'],
                    $sanitizedProps['after']['remember_token']
                );
            }

            $payload = array_merge([
                'module'      => $module,
                'action_type' => $actionType,
                'reference'   => $reference,
                'branch_id'   => $branchId,
                'branch_name' => $branchName,
                'role'        => $role,
                'outcome'     => $outcome,
                'username'    => $user?->name ?? ($sanitizedProps['attempted_email'] ?? 'System'),
                'user_email'  => $user?->email ?? ($sanitizedProps['attempted_email'] ?? null),
                'ip'          => request()?->ip(),
                'user_agent'  => request()?->userAgent(),
            ], $sanitizedProps);

            $activity = activity($module)
                ->event($actionType);

            if ($subject) {
                $activity->performedOn($subject);
            }

            if ($user) {
                $activity->causedBy($user);
            }

            return $activity->withProperties($payload)->log($description);
        } catch (\Throwable $e) {
            Log::warning("ActivityLogger failed: " . $e->getMessage(), [
                'module' => $module,
                'action' => $actionType,
            ]);
            return null;
        }
    }
}
