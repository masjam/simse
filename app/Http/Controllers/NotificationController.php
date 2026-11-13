<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = (string) $request->query('filter', 'all');
        $user = Auth::user();

        $query = $filter === 'unread'
            ? $user->unreadNotifications()
            : $user->notifications();

        $notifications = $query->latest()->paginate(20)->through(fn ($item): array => [
            'id' => $item->id,
            'title' => $item->data['title'] ?? 'Notifikasi',
            'message' => $item->data['message'] ?? '',
            'category' => $item->data['category'] ?? 'info',
            'url' => $item->data['url'] ?? null,
            'read_at' => $item->read_at?->format('d M Y, H:i'),
            'is_unread' => $item->read_at === null,
            'created_at_human' => $item->created_at?->diffForHumans() ?? '-',
            'created_at' => $item->created_at?->format('d M Y, H:i') ?? '-',
        ]);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'filter' => $filter,
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function poll(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'recent' => $user->notifications()
                ->latest()
                ->take(8)
                ->get()
                ->map(fn ($item): array => [
                    'id' => $item->id,
                    'title' => $item->data['title'] ?? 'Notifikasi',
                    'message' => $item->data['message'] ?? '',
                    'category' => $item->data['category'] ?? 'info',
                    'url' => $item->data['url'] ?? null,
                    'read_at' => $item->read_at?->toIso8601String(),
                    'created_at_human' => $item->created_at?->diffForHumans() ?? '-',
                ])
                ->values(),
        ]);
    }

    public function markAsRead(string $id): RedirectResponse
    {
        $notification = Auth::user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return back();
    }

    public function markAllAsRead(): RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi telah ditandai sudah dibaca.');
    }
}
