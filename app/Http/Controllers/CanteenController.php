<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Canteen\MenuController;
use App\Http\Controllers\Canteen\OrderController;
use App\Http\Controllers\Canteen\RfidPosController;
use App\Http\Requests\Canteen\RfidBalanceCheckRequest;
use App\Http\Requests\Canteen\RfidPaymentRequest;
use App\Http\Requests\Canteen\StoreMenuRequest;
use App\Http\Requests\Canteen\UpdateMenuRequest;
use App\Http\Requests\Canteen\UpdateOrderStatusRequest;
use App\Http\Requests\Canteen\ValidateQrRequest;
use App\Models\CanteenMenu;
use App\Models\CanteenOrder;
use App\Models\CanteenStand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CanteenController extends Controller
{
    private function resolveStand(): CanteenStand
    {
        $user = Auth::user();

        return CanteenStand::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => 'Stand '.$user->name,
                'description' => 'Stand makanan & minuman sehat sekolah',
                'balance' => 0,
                'is_open' => true,
            ]
        );
    }

    public function dashboard(): Response
    {
        $stand = $this->resolveStand();

        $todayOrdersQuery = CanteenOrder::where('canteen_stand_id', $stand->id)
            ->whereDate('created_at', today())
            ->where('status', '!=', 'Dibatalkan');

        $orders = CanteenOrder::with(['student.user', 'student.studentClass', 'items'])
            ->where('canteen_stand_id', $stand->id)
            ->latest()
            ->take(30)
            ->get()
            ->map(fn (CanteenOrder $order): array => [
                'id' => $order->id,
                'qr_code_pickup' => $order->qr_code_pickup,
                'student_name' => $order->student?->user?->name ?? 'Siswa',
                'class_name' => $order->student?->studentClass?->name ?? '-',
                'total_price' => (float) $order->total_price,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'created_at' => $order->created_at?->format('d M Y, H:i') ?? '-',
                'items' => $order->items->map(fn ($item): array => [
                    'id' => $item->id,
                    'menu_name' => $item->menu_name,
                    'quantity' => $item->quantity,
                    'subtotal' => (float) $item->subtotal,
                ]),
            ]);

        $summary = [
            'today_revenue' => (float) (clone $todayOrdersQuery)->sum('total_price'),
            'today_orders_count' => (clone $todayOrdersQuery)->count(),
            'pending_orders_count' => CanteenOrder::where('canteen_stand_id', $stand->id)
                ->whereIn('status', ['Menunggu Konfirmasi', 'Sedang Dimasak'])
                ->count(),
            'total_balance' => (float) $stand->balance,
        ];

        return Inertia::render('Canteen/Dashboard', [
            'stand' => [
                'id' => $stand->id,
                'name' => $stand->name,
                'description' => $stand->description,
                'is_open' => $stand->is_open,
                'balance' => (float) $stand->balance,
            ],
            'summary' => $summary,
            'orders' => $orders,
        ]);
    }

    public function toggleOpen(): RedirectResponse
    {
        $stand = $this->resolveStand();
        $stand->update(['is_open' => ! $stand->is_open]);

        $statusText = $stand->is_open ? 'dibuka' : 'ditutup sementara';

        return redirect()->back()->with('success', "Stand kantin berhasil {$statusText}.");
    }

    public function updateStand(Request $request): RedirectResponse
    {
        $stand = $this->resolveStand();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $stand->update($validated);

        return redirect()->back()->with('success', 'Profil stand berhasil diperbarui.');
    }

    public function menus(): Response
    {
        return app(MenuController::class)->index();
    }

    public function storeMenu(StoreMenuRequest $request): RedirectResponse
    {
        return app(MenuController::class)->store($request);
    }

    public function updateMenu(UpdateMenuRequest $request, CanteenMenu $menu): RedirectResponse
    {
        return app(MenuController::class)->update($request, $menu);
    }

    public function destroyMenu(CanteenMenu $menu): RedirectResponse
    {
        return app(MenuController::class)->destroy($menu);
    }

    public function updateOrderStatus(UpdateOrderStatusRequest $request, CanteenOrder $order): RedirectResponse
    {
        return app(OrderController::class)->updateStatus($request, $order);
    }

    public function scanQr(): Response
    {
        return app(OrderController::class)->scanQr();
    }

    public function validateQr(ValidateQrRequest $request): RedirectResponse
    {
        return app(OrderController::class)->validateQr($request);
    }

    public function rfidPos(): Response
    {
        return app(RfidPosController::class)->index();
    }

    public function checkRfidBalance(RfidBalanceCheckRequest $request): JsonResponse
    {
        return app(RfidPosController::class)->checkBalance($request);
    }

    public function payWithRfid(RfidPaymentRequest $request): RedirectResponse
    {
        return app(RfidPosController::class)->pay($request);
    }
}
