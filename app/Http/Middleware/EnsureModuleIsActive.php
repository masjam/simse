<?php

namespace App\Http\Middleware;

use App\Services\ModuleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleIsActive
{
    public function __construct(
        private readonly ModuleService $moduleService,
    ) {}

    public function handle(Request $request, Closure $next, string $moduleCode): Response
    {
        abort_unless(
            $this->moduleService->isActive($moduleCode),
            403,
            "Modul {$moduleCode} sedang dinonaktifkan oleh Administrator."
        );

        return $next($request);
    }
}
