<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() === 419) {
                if ($request->is('login') || $request->is('client/login') || $request->routeIs('login') || $request->routeIs('login.submit')) {
                    return redirect()->route('login')
                        ->withInput($request->only('email'))
                        ->with('status', 'Your session expired. Please sign in again.');
                }

                return redirect()->back()
                    ->withInput($request->except(['password', '_token']))
                    ->with('status', 'Your session expired. Please try again.');
            }
        });
    })->create();
