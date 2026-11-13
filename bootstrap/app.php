<?php

use App\Http\Middleware\EnsureKioskMacAllowed;
use App\Http\Middleware\EnsureModuleIsActive;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'module' => EnsureModuleIsActive::class,
            'kiosk.mac' => EnsureKioskMacAllowed::class,
        ]);
        $middleware->redirectGuestsTo(function (Request $request): string {
            if ($request->is('guru') || $request->is('guru/*')) {
                return route('role.login', ['role' => 'guru']);
            }
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('role.login', ['role' => 'admin']);
            }
            if ($request->is('bendahara') || $request->is('bendahara/*')) {
                return route('role.login', ['role' => 'bendahara']);
            }
            if ($request->is('staf') || $request->is('staf/*')) {
                return route('role.login', ['role' => 'staf']);
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
