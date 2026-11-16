<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    /**
     * Display a listing of system audit logs.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $event = $request->query('event');
        $userId = $request->query('user_id');
        $model = $request->query('model');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = AuditLog::with('user:id,name')
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('auditable_id', 'like', "%{$search}%")
                        ->orWhere('auditable_type', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%");
                });
            })
            ->when($event, fn ($q) => $q->where('event', $event))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($model, fn ($q) => $q->where('auditable_type', 'like', "%{$model}%"))
            ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('created_at', '<=', $endDate))
            ->orderByDesc('created_at');

        $logs = $query->paginate(20)->withQueryString()->through(function ($log) {
            $shortModel = class_basename($log->auditable_type);

            return [
                'id' => $log->id,
                'user' => $log->user ? [
                    'id' => $log->user->id,
                    'name' => $log->user->name,
                ] : [
                    'id' => null,
                    'name' => 'Sistem / Anonim',
                ],
                'event' => $log->event,
                'model' => $shortModel,
                'auditable_type' => $log->auditable_type,
                'auditable_id' => $log->auditable_id,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'created_at' => $log->created_at?->translatedFormat('d M Y H:i:s'),
            ];
        });

        $users = User::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Admin/Audit/Index', [
            'logs' => $logs,
            'users' => $users,
            'filters' => [
                'search' => $search ?? '',
                'event' => $event ?? '',
                'user_id' => $userId ?? '',
                'model' => $model ?? '',
                'start_date' => $startDate ?? '',
                'end_date' => $endDate ?? '',
            ],
        ]);
    }
}
