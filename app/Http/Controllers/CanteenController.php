<?php

namespace App\Http\Controllers;

use App\Http\Requests\Canteen\RfidBalanceCheckRequest;
use App\Http\Requests\Canteen\RfidPaymentRequest;
use App\Http\Requests\Canteen\StoreMenuRequest;
use App\Http\Requests\Canteen\UpdateMenuRequest;
use App\Http\Requests\Canteen\UpdateOrderStatusRequest;
use App\Http\Requests\Canteen\ValidateQrRequest;
use App\Models\CanteenMenu;
use App\Models\CanteenOrder;
use App\Models\CanteenOrderItem;
use App\Models\CanteenStand;
use App\Models\Student;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Notifications\SimseNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                ])->values(),
            ]);

        return Inertia::render('Canteen/Dashboard', [
            'stand' => [
                'id' => $stand->id,
                'name' => $stand->name,
                'description' => $stand->description,
                'balance' => (float) $stand->balance,
                'is_open' => $stand->is_open,
            ],
            'stats' => [
                'today_orders' => (clone $todayOrdersQuery)->count(),
                'today_revenue' => (float) (clone $todayOrdersQuery)->sum('total_price'),
                'pending_orders' => CanteenOrder::where('canteen_stand_id', $stand->id)
                    ->whereIn('status', ['Menunggu Konfirmasi', 'Sedang Dimasak', 'Siap Diambil'])
                    ->count(),
                'menu_count' => CanteenMenu::where('canteen_stand_id', $stand->id)->count(),
            ],
            'orders' => $orders,
        ]);
    }

    public function toggleOpen(): RedirectResponse
    {
        $stand = $this->resolveStand();
        $stand->update(['is_open' => ! $stand->is_open]);

        return redirect()->back()->with(
            'success',
            $stand->is_open ? 'Stand kantin dibuka untuk menerima pesanan.' : 'Stand kantin ditutup sementara.'
        );
    }

    public function updateStand(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:300'],
        ], [
            'name.required' => 'Nama stand wajib diisi.',
        ]);

        $stand = $this->resolveStand();
        $stand->update($validated);

        return redirect()->back()->with('success', 'Profil stand kantin berhasil diperbarui.');
    }

    public function menus(): Response
    {
        $stand = $this->resolveStand();

        $menus = CanteenMenu::where('canteen_stand_id', $stand->id)
            ->orderBy('name')
            ->get()
            ->map(fn (CanteenMenu $menu): array => [
                'id' => $menu->id,
                'name' => $menu->name,
                'price' => (float) $menu->price,
                'stock' => $menu->stock,
                'description' => $menu->description,
                'photo' => $menu->photo,
                'is_available' => $menu->is_available,
            ]);

        return Inertia::render('Canteen/Menus', [
            'stand' => [
                'id' => $stand->id,
                'name' => $stand->name,
                'is_open' => $stand->is_open,
            ],
            'menus' => $menus,
        ]);
    }

    public function storeMenu(StoreMenuRequest $request): RedirectResponse
    {
        $stand = $this->resolveStand();

        CanteenMenu::create([
            'canteen_stand_id' => $stand->id,
            ...$request->validated(),
        ]);

        return redirect()->back()->with('success', 'Menu baru berhasil ditambahkan ke stand.');
    }

    public function updateMenu(UpdateMenuRequest $request, CanteenMenu $menu): RedirectResponse
    {
        $stand = $this->resolveStand();
        if ($menu->canteen_stand_id !== $stand->id) {
            abort(403, 'Anda tidak berhak mengubah menu milik stand lain.');
        }

        $menu->update($request->validated());

        return redirect()->back()->with('success', 'Data menu berhasil diperbarui.');
    }

    public function destroyMenu(CanteenMenu $menu): RedirectResponse
    {
        $stand = $this->resolveStand();
        if ($menu->canteen_stand_id !== $stand->id) {
            abort(403, 'Anda tidak berhak menghapus menu milik stand lain.');
        }

        $menu->delete();

        return redirect()->back()->with('success', 'Menu berhasil dihapus.');
    }

    public function updateOrderStatus(UpdateOrderStatusRequest $request, CanteenOrder $order): RedirectResponse
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

    public function rfidPos(): Response
    {
        $stand = $this->resolveStand();

        $menus = CanteenMenu::where('canteen_stand_id', $stand->id)
            ->where('is_available', true)
            ->orderBy('name')
            ->get()
            ->map(fn (CanteenMenu $menu): array => [
                'id' => $menu->id,
                'name' => $menu->name,
                'price' => (float) $menu->price,
                'stock' => $menu->stock,
            ]);

        return Inertia::render('Canteen/RfidPos', [
            'stand' => [
                'id' => $stand->id,
                'name' => $stand->name,
                'is_open' => $stand->is_open,
                'balance' => (float) $stand->balance,
            ],
            'menus' => $menus,
        ]);
    }

    public function checkRfidBalance(RfidBalanceCheckRequest $request): JsonResponse
    {
        $uid = trim((string) $request->validated('rfid_uid'));

        $student = Student::with(['user', 'studentClass'])
            ->where('rfid_uid', $uid)
            ->first();

        if (! $student || ! $student->user) {
            return response()->json([
                'message' => "Kartu RFID ({$uid}) belum terdaftar pada data siswa.",
            ], 404);
        }

        $wallet = Wallet::firstOrCreate(
            ['student_id' => $student->id],
            ['balance' => 0]
        );

        $todaySpent = (float) WalletTransaction::where('wallet_id', $wallet->id)
            ->where('type', 'Pembayaran Kantin')
            ->where('status', 'Sukses')
            ->whereDate('created_at', today())
            ->sum('amount');

        $dailyLimit = $wallet->daily_limit !== null ? (float) $wallet->daily_limit : null;

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->user->name,
                'nisn' => $student->nisn,
                'class_name' => $student->studentClass?->name ?? '-',
                'rfid_uid' => $student->rfid_uid,
            ],
            'wallet' => [
                'balance' => (float) $wallet->balance,
                'has_pin' => ! empty($wallet->pin),
                'daily_limit' => $dailyLimit,
                'today_spent' => $todaySpent,
                'remaining_daily_limit' => $dailyLimit !== null ? max(0, $dailyLimit - $todaySpent) : null,
            ],
        ]);
    }

    public function payWithRfid(RfidPaymentRequest $request): RedirectResponse
    {
        $stand = $this->resolveStand();
        $validated = $request->validated();
        $uid = trim((string) $validated['rfid_uid']);

        $student = Student::with('user')->where('rfid_uid', $uid)->first();
        if (! $student) {
            throw ValidationException::withMessages([
                'rfid_uid' => "Kartu RFID ({$uid}) tidak terdaftar.",
            ]);
        }

        $order = DB::transaction(function () use ($stand, $student, $validated): CanteenOrder {
            $lockedStand = CanteenStand::where('id', $stand->id)->lockForUpdate()->firstOrFail();
            if (! $lockedStand->is_open) {
                throw ValidationException::withMessages([
                    'rfid_uid' => 'Stand kantin sedang tutup.',
                ]);
            }

            $wallet = Wallet::where('student_id', $student->id)->lockForUpdate()->first();
            if (! $wallet || empty($wallet->pin)) {
                throw ValidationException::withMessages([
                    'pin' => 'PIN dompet kartu siswa belum diatur. Hubungi Bendahara.',
                ]);
            }

            if (! Hash::check($validated['pin'], $wallet->pin)) {
                throw ValidationException::withMessages([
                    'pin' => 'PIN 6 digit kartu siswa tidak sesuai.',
                ]);
            }

            $groupedItems = [];
            foreach ($validated['items'] as $entry) {
                $groupedItems[$entry['menu_id']] = ($groupedItems[$entry['menu_id']] ?? 0) + (int) $entry['quantity'];
            }

            $menus = CanteenMenu::whereIn('id', array_keys($groupedItems))
                ->where('canteen_stand_id', $lockedStand->id)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalPrice = 0.0;
            $prepared = [];

            foreach ($groupedItems as $menuId => $qty) {
                $menu = $menus->get($menuId);
                if (! $menu || ! $menu->is_available) {
                    throw ValidationException::withMessages([
                        'items' => 'Menu tidak tersedia pada stand Anda.',
                    ]);
                }

                if ($menu->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$menu->name} tidak mencukupi (tersisa {$menu->stock}).",
                    ]);
                }

                $subtotal = (float) $menu->price * $qty;
                $totalPrice += $subtotal;
                $prepared[] = [
                    'menu' => $menu,
                    'quantity' => $qty,
                    'unit_price' => (float) $menu->price,
                    'subtotal' => $subtotal,
                ];
            }

            if ((float) $wallet->balance < $totalPrice) {
                throw ValidationException::withMessages([
                    'pin' => 'Saldo tabungan kartu siswa tidak mencukupi.',
                ]);
            }

            if ($wallet->daily_limit !== null && (float) $wallet->daily_limit > 0) {
                $todaySpent = (float) WalletTransaction::where('wallet_id', $wallet->id)
                    ->where('type', 'Pembayaran Kantin')
                    ->where('status', 'Sukses')
                    ->whereDate('created_at', today())
                    ->sum('amount');

                if (($todaySpent + $totalPrice) > (float) $wallet->daily_limit) {
                    throw ValidationException::withMessages([
                        'pin' => 'Transaksi melebihi batas pengeluaran harian yang diatur orang tua.',
                    ]);
                }
            }

            $wallet->decrement('balance', $totalPrice);
            $lockedStand->increment('balance', $totalPrice);

            $order = CanteenOrder::create([
                'canteen_stand_id' => $lockedStand->id,
                'student_id' => $student->id,
                'total_price' => $totalPrice,
                'status' => 'Selesai',
                'payment_method' => 'Dompet Digital',
                'qr_code_pickup' => 'RFID-'.strtoupper(Str::random(8)),
            ]);

            foreach ($prepared as $item) {
                $item['menu']->decrement('stock', $item['quantity']);

                CanteenOrderItem::create([
                    'canteen_order_id' => $order->id,
                    'canteen_menu_id' => $item['menu']->id,
                    'menu_name' => $item['menu']->name,
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'Pembayaran Kantin',
                'amount' => $totalPrice,
                'reference_id' => $order->id,
                'description' => "Pembayaran ATM RFID di {$lockedStand->name} ({$order->qr_code_pickup})",
                'status' => 'Sukses',
            ]);

            return $order;
        });

        $studentName = $student->user?->name ?? 'Siswa';

        return redirect()->back()->with(
            'success',
            "Pembayaran RFID berhasil untuk {$studentName} sebesar Rp ".number_format($order->total_price, 0, ',', '.').'.'
        );
    }
}
