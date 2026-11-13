<?php

namespace App\Http\Controllers\Canteen;

use App\Http\Requests\Canteen\UpdateOrderStatusRequest;
use App\Http\Requests\Canteen\ValidateQrRequest;
use App\Models\CanteenMenu;
use App\Models\CanteenOrder;
use App\Models\CanteenStand;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Notifications\SimseNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends BaseCanteenController
{
    public function updateStatus(UpdateOrderStatusRequest $request, CanteenOrder $order): RedirectResponse
    {
        $stand = $this->resolveStand();
        if ($order->canteen_stand_id !== $stand->id) {
            abort(403, 'Anda tidak berhak mengelola pesanan milik stand lain.');
        }

        $nextStatus = $request->validated('status');

        if ($order->status === 'Dibatalkan' || $order->status === 'Selesai') {
            return redirect()->back()->with('error', 'Pesanan yang sudah selesai atau dibatalkan tidak dapat diubah lagi.');
        }

        DB::transaction(function () use ($order, $stand, $nextStatus): void {
            $lockedOrder = CanteenOrder::with(['items', 'student.user'])
                ->where('id', $order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($nextStatus === 'Dibatalkan' && $lockedOrder->payment_method === 'Dompet Digital') {
                foreach ($lockedOrder->items as $item) {
                    CanteenMenu::where('id', $item->canteen_menu_id)->increment('stock', $item->quantity);
                }

                $wallet = Wallet::where('student_id', $lockedOrder->student_id)->lockForUpdate()->first();
                if ($wallet) {
                    $wallet->increment('balance', $lockedOrder->total_price);

                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'type' => 'Setoran',
                        'amount' => $lockedOrder->total_price,
                        'reference_id' => $lockedOrder->id,
                        'description' => "Pengembalian dana pesanan kantin dibatalkan ({$lockedOrder->qr_code_pickup})",
                        'status' => 'Sukses',
                    ]);
                }

                $lockedStand = CanteenStand::where('id', $stand->id)->lockForUpdate()->firstOrFail();
                $lockedStand->update([
                    'balance' => max(0, (float) $lockedStand->balance - (float) $lockedOrder->total_price),
                ]);
            }

            $lockedOrder->update(['status' => $nextStatus]);

            if ($lockedOrder->student?->user) {
                $lockedOrder->student->user->notify(new SimseNotification(
                    title: "Pesanan Kantin: {$nextStatus}",
                    message: "Pesanan {$lockedOrder->qr_code_pickup} di {$stand->name} berstatus {$nextStatus}.",
                    category: 'canteen',
                    url: route('siswa.kantin'),
                ));
            }
        });

        return redirect()->back()->with('success', "Status pesanan berhasil diubah menjadi {$nextStatus}.");
    }

    public function scanQr(): Response
    {
        $stand = $this->resolveStand();

        $readyOrders = CanteenOrder::with(['student.user', 'items'])
            ->where('canteen_stand_id', $stand->id)
            ->whereIn('status', ['Menunggu Konfirmasi', 'Sedang Dimasak', 'Siap Diambil'])
            ->latest()
            ->take(15)
            ->get()
            ->map(fn (CanteenOrder $order): array => [
                'id' => $order->id,
                'qr_code_pickup' => $order->qr_code_pickup,
                'student_name' => $order->student?->user?->name ?? 'Siswa',
                'total_price' => (float) $order->total_price,
                'status' => $order->status,
                'items_summary' => $order->items->map(fn ($i): string => "{$i->menu_name} x{$i->quantity}")->implode(', '),
            ]);

        return Inertia::render('Canteen/ScanQr', [
            'stand' => [
                'id' => $stand->id,
                'name' => $stand->name,
            ],
            'readyOrders' => $readyOrders,
        ]);
    }

    public function validateQr(ValidateQrRequest $request): RedirectResponse
    {
        $stand = $this->resolveStand();
        $code = trim((string) $request->validated('qr_code'));

        $order = CanteenOrder::with(['student.user', 'items'])
            ->where('canteen_stand_id', $stand->id)
            ->where('qr_code_pickup', $code)
            ->first();

        if (! $order) {
            return redirect()->back()->with('error', "Kode QR {$code} tidak ditemukan pada stand Anda.");
        }

        if ($order->status === 'Dibatalkan') {
            return redirect()->back()->with('error', "Pesanan {$code} sudah dibatalkan.");
        }

        if ($order->status === 'Selesai') {
            return redirect()->back()->with('error', "Pesanan {$code} sudah diambil sebelumnya.");
        }

        $order->update(['status' => 'Selesai']);

        $studentName = $order->student?->user?->name ?? 'Siswa';

        return redirect()->back()->with(
            'success',
            "Validasi QR berhasil! Pesanan {$code} atas nama {$studentName} telah diselesaikan."
        );
    }
}
