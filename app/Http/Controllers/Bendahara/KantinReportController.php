<?php

namespace App\Http\Controllers\Bendahara;

use App\Models\CanteenOrder;
use App\Models\CanteenStand;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KantinReportController extends BaseBendaharaController
{
    public function index(Request $request): Response
    {
        $startDate = (string) $request->query('start_date', today()->startOfMonth()->toDateString());
        $endDate = (string) $request->query('end_date', today()->toDateString());

        $stands = CanteenStand::with('owner')
            ->orderBy('name')
            ->get()
            ->map(function (CanteenStand $stand) use ($startDate, $endDate): array {
                $ordersQuery = CanteenOrder::where('canteen_stand_id', $stand->id)
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);

                $validOrders = (clone $ordersQuery)->where('status', '!=', 'Dibatalkan');
                $completedOrders = (clone $ordersQuery)->where('status', 'Selesai');

                return [
                    'id' => $stand->id,
                    'name' => $stand->name,
                    'owner_name' => $stand->owner?->name ?? 'Pengelola Stand',
                    'is_open' => $stand->is_open,
                    'balance' => (float) $stand->balance,
                    'total_orders' => (clone $validOrders)->count(),
                    'completed_orders' => (clone $completedOrders)->count(),
                    'total_revenue' => (float) (clone $validOrders)->sum('total_price'),
                ];
            })
            ->values();

        $recentOrders = CanteenOrder::with(['stand', 'student.user', 'items'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->latest()
            ->take(30)
            ->get()
            ->map(fn (CanteenOrder $order): array => [
                'id' => $order->id,
                'qr_code_pickup' => $order->qr_code_pickup,
                'stand_name' => $order->stand?->name ?? '-',
                'student_name' => $order->student?->user?->name ?? 'Siswa',
                'total_price' => (float) $order->total_price,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'created_at' => $order->created_at?->format('d M Y, H:i') ?? '-',
                'items_summary' => $order->items->map(fn ($i): string => "{$i->menu_name} x{$i->quantity}")->implode(', '),
            ])
            ->values();

        return Inertia::render('Bendahara/KantinReport', [
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'total_stands' => $stands->count(),
                'total_orders' => $stands->sum('total_orders'),
                'total_revenue' => (float) $stands->sum('total_revenue'),
                'total_stand_balance' => (float) $stands->sum('balance'),
            ],
            'stands' => $stands,
            'recentOrders' => $recentOrders,
        ]);
    }
}
