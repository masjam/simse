<?php

namespace App\Http\Middleware;

use App\Services\ModuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $notificationsSummary = [
            'unread_count' => 0,
            'recent' => [],
        ];

        if ($user && Schema::hasTable('notifications')) {
            $notificationsSummary = [
                'unread_count' => $user->unreadNotifications()->count(),
                'recent' => $user->notifications()
                    ->latest()
                    ->take(6)
                    ->get()
                    ->map(fn ($notification): array => [
                        'id' => $notification->id,
                        'title' => $notification->data['title'] ?? 'Notifikasi',
                        'message' => $notification->data['message'] ?? '',
                        'category' => $notification->data['category'] ?? 'info',
                        'url' => $notification->data['url'] ?? null,
                        'read_at' => $notification->read_at?->toIso8601String(),
                        'created_at_human' => $notification->created_at?->diffForHumans() ?? '-',
                    ])
                    ->values()
                    ->all(),
            ];
        }

        $defaultTheme = config('theme.default', 'madinah');
        if ($user) {
            $userRole = $user->getRoleNames()->first();
            $defaultTheme = config("theme.roles.{$userRole}", $defaultTheme);
        }
        $activeTheme = $request->cookie('simse_theme') ?: ($request->session()->get('theme') ?: $defaultTheme);

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? array_merge(
                    $user->toArray(),
                    ['roles' => $user->getRoleNames()]
                ) : null,
            ],
            'activeModules' => app(ModuleService::class)->activeMap(),
            'notificationsSummary' => $notificationsSummary,
            'theme' => $activeTheme,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
        ];
    }
}
