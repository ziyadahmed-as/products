<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ActivityLogger;

class UserObserver
{
    public function created(User $user): void
    {
        ActivityLogger::log(
            module: 'Users',
            actionType: 'create',
            description: "User {$user->name} ({$user->email}) was created.",
            subject: $user,
            properties: [
                'user_id'   => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'is_active' => $user->is_active,
            ]
        );
    }

    public function updated(User $user): void
    {
        $dirty = $user->getDirty();
        // Remove sensitive fields
        unset($dirty['password'], $dirty['remember_token']);

        if (!empty($dirty)) {
            $original = array_intersect_key($user->getOriginal(), $dirty);
            $action = isset($dirty['is_active']) ? ($dirty['is_active'] ? 'activated' : 'deactivated') : 'update';

            ActivityLogger::log(
                module: 'Users',
                actionType: $action,
                description: "User {$user->name} ({$user->email}) was updated.",
                subject: $user,
                properties: [
                    'user_id' => $user->id,
                    'before'  => $original,
                    'after'   => $dirty,
                ]
            );
        }
    }

    public function deleted(User $user): void
    {
        ActivityLogger::log(
            module: 'Users',
            actionType: 'delete',
            description: "User {$user->name} ({$user->email}) was deleted.",
            subject: $user,
            properties: [
                'user_id' => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
            ]
        );
    }
}
