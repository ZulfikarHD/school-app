<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\LogActivity;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/auth.php'));
        },
    )
    ->withSchedule(function (Schedule $schedule): void {
        // Process notification queue every 5 minutes
        $schedule->command('notifications:process')
            ->everyFiveMinutes()
            ->withoutOverlapping();

        // Check daily alpha at 15:00 WIB (end of school day)
        $schedule->command('attendance:check-alpha')
            ->dailyAt('15:00')
            ->timezone('Asia/Jakarta');

        // Send reminders at 10:00 WIB if attendance not yet recorded
        $schedule->command('attendance:send-reminders')
            ->dailyAt('10:00')
            ->timezone('Asia/Jakarta')
            ->weekdays();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            SecurityHeaders::class,
            LogActivity::class,
        ]);

        $middleware->alias([
            'role' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
