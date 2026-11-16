<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
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

        $routeName = $request->route()?->getName();
        $defaultTheme = config('theme.default', 'madinah');

        if ($routeName && ($routeTheme = config("theme.routes.{$routeName}"))) {
            $defaultTheme = $routeTheme;
        } elseif ($user) {
            $userRole = $user->getRoleNames()->first();
            $defaultTheme = config("theme.roles.{$userRole}", $defaultTheme);

            // Siswa tingkat 1-3 otomatis menggunakan tema taman-belajar
            if ($user->hasRole('Siswa')) {
                $studentLevel = $user->student?->studentClass?->level;
                if ($studentLevel && $studentLevel <= 3) {
                    $defaultTheme = 'taman-belajar';
                }
            }
        }

        $rawSetting = SystemSetting::getValue('theme_allow_user_switch', config('theme.allow_user_switch', true));
        $allowUserSwitch = filter_var($rawSetting, FILTER_VALIDATE_BOOLEAN);

        if ($allowUserSwitch) {
            $activeTheme = $request->cookie('simse_theme') ?: ($request->session()->get('theme') ?: $defaultTheme);
        } else {
            $activeTheme = $defaultTheme;
        }

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
            'allowThemeSwitch' => $allowUserSwitch,
            'availableThemes' => config('theme.available', []),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
        ];
    }
}
