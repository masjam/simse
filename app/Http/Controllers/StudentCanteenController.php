<?php

namespace App\Http\Controllers;

use App\Http\Requests\Canteen\StudentCheckoutRequest;
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

class StudentCanteenController extends Controller
{
    private function currentStudent(): Student
    {
        return Student::with(['user', 'studentClass'])
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    public function index(): Response
    {
        $student = $this->currentStudent();
        $wallet = Wallet::firstOrCreate(
            ['student_id' => $student->id],
            ['balance' => 0]
        );

        $todaySpent = (float) WalletTransaction::where('wallet_id', $wallet->id)
            ->where('type', 'Pembayaran Kantin')
            ->where('status', 'Sukses')
            ->whereDate('created_at', today())
            ->sum('amount');

        $stands = CanteenStand::with([
            'menus' => fn ($query) => $query->where('is_available', true)->orderBy('name'),
        ])
            ->orderByDesc('is_open')
            ->orderBy('name')
            ->get()
            ->map(fn (CanteenStand $stand): array => [
                'id' => $stand->id,
                'name' => $stand->name,
                'description' => $stand->description,
                'is_open' => $stand->is_open,
                'menus' => $stand->menus->map(fn (CanteenMenu $menu): array => [
                    'id' => $menu->id,
                    'name' => $menu->name,
                    'price' => (float) $menu->price,
                    'stock' => $menu->stock,
                    'description' => $menu->description,
                    'photo' => $menu->photo,
                ])->values(),
            ]);

        $orders = CanteenOrder::with(['stand', 'items'])
            ->where('student_id', $student->id)
            ->latest()
            ->take(20)
            ->get()
            ->map(fn (CanteenOrder $order): array => [
                'id' => $order->id,
                'stand_name' => $order->stand?->name ?? 'Stand Kantin',
                'total_price' => (float) $order->total_price,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'qr_code_pickup' => $order->qr_code_pickup,
                'created_at' => $order->created_at?->format('d M Y, H:i') ?? '-',
                'items' => $order->items->map(fn ($item): array => [
                    'id' => $item->id,
                    'menu_name' => $item->menu_name,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                ])->values(),
            ]);

        return Inertia::render('Student/Kantin', [
            'stands' => $stands,
            'wallet' => [
                'balance' => (float) $wallet->balance,
                'has_pin' => ! empty($wallet->pin),
                'daily_limit' => $wallet->daily_limit !== null ? (float) $wallet->daily_limit : null,
                'today_spent' => $todaySpent,
            ],
            'orders' => $orders,
        ]);
    }

    public function checkout(StudentCheckoutRequest $request): RedirectResponse
    {
        $student = $this->currentStudent();
        $validated = $request->validated();

        $order = DB::transaction(function () use ($student, $validated): CanteenOrder {
            $wallet = Wallet::where('student_id', $student->id)
                ->lockForUpdate()
                ->first();

            if (! $wallet || empty($wallet->pin)) {
                throw ValidationException::withMessages([
                    'pin' => 'PIN dompet digital Anda belum diatur. Hubungi Bendahara untuk aktivasi PIN.',
                ]);
            }

            if (! Hash::check($validated['pin'], $wallet->pin)) {
                throw ValidationException::withMessages([
                    'pin' => 'PIN dompet digital tidak sesuai.',
                ]);
            }

            $stand = CanteenStand::with('owner')
                ->where('id', $validated['canteen_stand_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (! $stand->is_open) {
                throw ValidationException::withMessages([
                    'canteen_stand_id' => 'Stand kantin sedang tutup dan tidak menerima pesanan.',
                ]);
            }

            $groupedItems = [];
            foreach ($validated['items'] as $entry) {
                $menuId = $entry['menu_id'];
                $groupedItems[$menuId] = ($groupedItems[$menuId] ?? 0) + (int) $entry['quantity'];
            }

            $menus = CanteenMenu::whereIn('id', array_keys($groupedItems))
                ->where('canteen_stand_id', $stand->id)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalPrice = 0.0;
            $preparedItems = [];

            foreach ($groupedItems as $menuId => $qty) {
                $menu = $menus->get($menuId);
                if (! $menu || ! $menu->is_available) {
                    throw ValidationException::withMessages([
                        'items' => 'Sebagian menu tidak tersedia atau bukan milik stand yang dipilih.',
                    ]);
                }

                if ($menu->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$menu->name} tidak mencukupi (tersisa {$menu->stock}).",
                    ]);
                }

                $subtotal = (float) $menu->price * $qty;
                $totalPrice += $subtotal;

                $preparedItems[] = [
                    'menu' => $menu,
                    'quantity' => $qty,
                    'unit_price' => (float) $menu->price,
                    'subtotal' => $subtotal,
                ];
            }

            if ((float) $wallet->balance < $totalPrice) {
                throw ValidationException::withMessages([
                    'pin' => 'Saldo dompet digital Anda tidak mencukupi untuk transaksi ini.',
                ]);
            }

            if ($wallet->daily_limit !== null && (float) $wallet->daily_limit > 0) {
                $todaySpent = (float) WalletTransaction::where('wallet_id', $wallet->id)
                    ->where('type', 'Pembayaran Kantin')
                    ->where('status', 'Sukses')
                    ->whereDate('created_at', today())
                    ->sum('amount');

                if (($todaySpent + $totalPrice) > (float) $wallet->daily_limit) {
                    $remaining = max(0, (float) $wallet->daily_limit - $todaySpent);
                    throw ValidationException::withMessages([
                        'pin' => 'Transaksi melebihi batas pengeluaran harian kantin (sisa kuota hari ini: Rp '.number_format($remaining, 0, ',', '.').').',
                    ]);
                }
            }

            $wallet->decrement('balance', $totalPrice);
            $stand->increment('balance', $totalPrice);

            $order = CanteenOrder::create([
                'canteen_stand_id' => $stand->id,
                'student_id' => $student->id,
                'total_price' => $totalPrice,
                'status' => 'Menunggu Konfirmasi',
                'payment_method' => 'Dompet Digital',
                'qr_code_pickup' => 'KNT-'.strtoupper(Str::random(10)),
            ]);

            foreach ($preparedItems as $item) {
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
                'description' => "Belanja e-Kantin di {$stand->name} ({$order->qr_code_pickup})",
                'status' => 'Sukses',
            ]);

            if ($stand->owner) {
                $studentName = $student->user?->name ?? 'Siswa';
                $stand->owner->notify(new SimseNotification(
                    title: "Pesanan Baru ({$order->qr_code_pickup})",
                    message: "{$studentName} memesan senilai Rp ".number_format($totalPrice, 0, ',', '.').'.',
                    category: 'canteen',
                    url: route('kantin.dashboard'),
                ));
            }

            return $order;
        });

        return redirect()->back()->with(
            'success',
            "Pesanan {$order->qr_code_pickup} berhasil dibayar! Tunjukkan QR code ke stand saat mengambil pesanan."
        );
    }

    public function checkBalanceWithPin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pin' => ['required', 'digits:6'],
        ], [
            'pin.required' => 'PIN 6 digit wajib diisi untuk mengecek saldo.',
            'pin.digits' => 'PIN harus berupa 6 digit angka.',
        ]);

        $student = $this->currentStudent();
        $wallet = Wallet::firstOrCreate(
            ['student_id' => $student->id],
            ['balance' => 0]
        );

        if (empty($wallet->pin)) {
            return response()->json([
                'message' => 'PIN dompet digital belum diatur. Hubungi Bendahara untuk aktivasi PIN.',
            ], 422);
        }

        if (! Hash::check($validated['pin'], $wallet->pin)) {
            return response()->json([
                'message' => 'PIN 6 digit tidak sesuai.',
            ], 422);
        }

        $todaySpent = (float) WalletTransaction::where('wallet_id', $wallet->id)
            ->where('type', 'Pembayaran Kantin')
            ->where('status', 'Sukses')
            ->whereDate('created_at', today())
            ->sum('amount');

        $transactions = WalletTransaction::where('wallet_id', $wallet->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(fn (WalletTransaction $tx): array => [
                'id' => $tx->id,
                'type' => $tx->type,
                'amount' => (float) $tx->amount,
                'description' => $tx->description ?? '-',
                'status' => $tx->status,
                'created_at' => $tx->created_at?->format('d M Y, H:i') ?? '-',
            ])
            ->values();

        return response()->json([
            'balance' => (float) $wallet->balance,
            'daily_limit' => $wallet->daily_limit !== null ? (float) $wallet->daily_limit : null,
            'today_spent' => $todaySpent,
            'rfid_uid' => $student->rfid_uid,
            'transactions' => $transactions,
        ]);
    }
}
