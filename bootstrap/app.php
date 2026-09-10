<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn (\Illuminate\Http\Request $request) => \App\Support\FrontendAuth::guestRedirect($request));
        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            if ($request->user()?->isAdmin()) {
                return route('admin.dashboard');
            }
            $webinar = \App\Support\FrontendAuth::webinar($request);
            if ($webinar) {
                return route('webinars.dashboard', $webinar);
            }
            return route('dashboard');
        });
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'admin.access' => \App\Http\Middleware\EnsureAdminAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session expired. Please refresh and try again.',
                    'csrf_token' => csrf_token(),
                ], 419);
            }
            return redirect()->back()
                ->withInput($request->except('_token', 'password', 'password_confirmation'))
                ->withErrors(['login' => 'Your session was refreshed. Please try again.']);
        });
    })->create();
