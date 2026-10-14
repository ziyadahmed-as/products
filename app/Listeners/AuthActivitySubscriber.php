<?php

namespace App\Listeners;

use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Events\Dispatcher;

class AuthActivitySubscriber
{
    public function handleLogin(Login $event): void
    {
        $user = $event->user;
        ActivityLogger::log(
            module: 'Auth',
            actionType: 'login',
            description: "User {$user->name} ({$user->email}) logged in successfully.",
            subject: $user,
            causer: $user,
            properties: [
                'user_id' => $user->id,
                'email'   => $user->email,
            ],
            outcome: 'success'
        );
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            $user = $event->user;
            ActivityLogger::log(
                module: 'Auth',
                actionType: 'logout',
                description: "User {$user->name} ({$user->email}) logged out.",
                subject: $user,
                causer: $user,
                properties: [
                    'user_id' => $user->id,
                    'email'   => $user->email,
                ],
                outcome: 'success'
            );
        }
    }

    public function handleFailed(Failed $event): void
    {
        $email = $event->credentials['email'] ?? 'unknown';
        ActivityLogger::log(
            module: 'Auth',
            actionType: 'failed_login',
            description: "Failed login attempt for email '{$email}'.",
            subject: $event->user,
            causer: null,
            properties: [
                'attempted_email' => $email,
            ],
            outcome: 'failed'
        );
    }

    public function handleRegistered(Registered $event): void
    {
        $user = $event->user;
        ActivityLogger::log(
            module: 'Auth',
            actionType: 'register',
            description: "New user account registered: {$user->name} ({$user->email}).",
            subject: $user,
            causer: $user,
            properties: [
                'user_id' => $user->id,
                'email'   => $user->email,
            ],
            outcome: 'success'
        );
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        $user = $event->user;
        ActivityLogger::log(
            module: 'Auth',
            actionType: 'password_reset',
            description: "Password reset completed for {$user->email}.",
            subject: $user,
            causer: $user,
            properties: [
                'user_id' => $user->id,
                'email'   => $user->email,
            ],
            outcome: 'success'
        );
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class         => 'handleLogin',
            Logout::class        => 'handleLogout',
            Failed::class        => 'handleFailed',
            Registered::class    => 'handleRegistered',
            PasswordReset::class => 'handlePasswordReset',
        ];
    }
}
