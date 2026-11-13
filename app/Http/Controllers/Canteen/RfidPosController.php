<?php

namespace App\Http\Controllers\Canteen;

use App\Http\Requests\Canteen\RfidBalanceCheckRequest;
use App\Http\Requests\Canteen\RfidPaymentRequest;
use App\Models\CanteenMenu;
use App\Models\CanteenOrder;
use App\Models\CanteenOrderItem;
use App\Models\CanteenStand;
use App\Models\Student;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RfidPosController extends BaseCanteenController
{
    public function index(): Response
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

    public function checkBalance(RfidBalanceCheckRequest $request): JsonResponse
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

    public function pay(RfidPaymentRequest $request): RedirectResponse
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
