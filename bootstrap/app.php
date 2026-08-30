<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Exclude Stripe webhook from CSRF protection
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);

        // Spec 30.1 — every authenticated web request resolves its account and
        // sets the Spatie team context. This never denies, so users part-way
        // through onboarding can still reach the onboarding screens.
        $middleware->web(append: [
            \App\Http\Middleware\SetAccountContext::class,
        ]);

        $middleware->redirectTo(
            guests: '/login',
            users: function (Request $request) {
                $user = $request->user();
                if (! $user) {
                    return '/';
                }
                if ($user->isAdmin()) {
                    return route('admin.dashboard');
                }
                if ($user->isSupplier()) {
                    return route('supplier.dashboard');
                }
                if ($user->isBuyer()) {
                    return route('buyer.dashboard');
                }
                return '/';
            }
        );

        // Spec 4.7 — opt-in enforcement for routes that need a usable account:
        //   ->middleware('account.context')           active user + account + membership
        //   ->middleware('account.context:buyer')     ... plus an active Buyer capability
        //   ->middleware('account.context:supplier')  ... plus an active Supplier capability
        $middleware->alias([
            'account.context' => \App\Http\Middleware\EnsureAccountContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
