<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\CanteenMenu;
use App\Models\CanteenOrder;
use App\Models\CanteenStand;
use App\Models\Module;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\User;
use App\Models\Wallet;
use App\Services\ModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint4CanteenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Super Admin', 'Pengelola Stand Kantin', 'Bendahara', 'Siswa', 'Orang Tua'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    /**
     * @return array{0: User, 1: Student, 2: Wallet}
     */
    private function createStudentWithWallet(float $balance = 100000, string $pin = '123456', ?User $parent = null): array
    {
        $studentUser = User::factory()->create(['name' => 'Naufal Hakim']);
        $studentUser->assignRole('Siswa');

        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2026/2027', 'semester' => 'Ganjil'],
            ['is_active' => true]
        );
        $class = StudentClass::firstOrCreate(
            ['academic_year_id' => $academicYear->id, 'name' => '4A'],
            ['level' => 4]
        );

        $student = Student::create([
            'user_id' => $studentUser->id,
            'parent_user_id' => $parent?->id,
            'student_class_id' => $class->id,
            'nis' => (string) random_int(10000, 99999),
            'nisn' => (string) random_int(1000000000, 9999999999),
            'nik' => (string) random_int(3403010000000000, 3403019999999999),
        ]);

        $wallet = Wallet::create([
            'student_id' => $student->id,
            'balance' => $balance,
            'pin' => $pin,
        ]);

        return [$studentUser, $student, $wallet];
    }

    public function test_canteen_owner_can_crud_own_menus_and_cannot_modify_other_stand_menu(): void
    {
        $owner1 = User::factory()->create(['name' => 'Ibu Siti']);
        $owner1->assignRole('Pengelola Stand Kantin');

        $owner2 = User::factory()->create(['name' => 'Pak Budi']);
        $owner2->assignRole('Pengelola Stand Kantin');

        $stand2 = CanteenStand::create([
            'user_id' => $owner2->id,
            'name' => 'Stand Pak Budi',
            'is_open' => true,
        ]);
        $otherMenu = CanteenMenu::create([
            'canteen_stand_id' => $stand2->id,
            'name' => 'Bakso Urat',
            'price' => 12000,
            'stock' => 10,
            'is_available' => true,
        ]);

        $this->actingAs($owner1)
            ->get(route('kantin.menus'))
            ->assertOk();

        $this->actingAs($owner1)
            ->post(route('kantin.menus.store'), [
                'name' => 'Nasi Goreng Telur',
                'price' => 10000,
                'stock' => 20,
                'description' => 'Menu sehat',
                'is_available' => true,
            ])
            ->assertRedirect();

        $ownMenu = CanteenMenu::where('name', 'Nasi Goreng Telur')->firstOrFail();

        $this->actingAs($owner1)
            ->put(route('kantin.menus.update', $ownMenu), [
                'name' => 'Nasi Goreng Spesial',
                'price' => 12000,
                'stock' => 15,
                'description' => 'Tanpa MSG',
                'is_available' => true,
            ])
            ->assertRedirect();

        $this->assertSame(12000.0, (float) $ownMenu->fresh()->price);

        $this->actingAs($owner1)
            ->put(route('kantin.menus.update', $otherMenu), [
                'name' => 'Hack',
                'price' => 1000,
                'stock' => 1,
                'is_available' => true,
            ])
            ->assertForbidden();

        $this->actingAs($owner1)
            ->delete(route('kantin.menus.destroy', $ownMenu))
            ->assertRedirect();

        $this->assertDatabaseMissing('canteen_menus', ['id' => $ownMenu->id]);
    }

    public function test_student_can_checkout_atomically_with_valid_pin_and_rejects_invalid_pin_or_over_limit(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Pengelola Stand Kantin');

        $stand = CanteenStand::create([
            'user_id' => $owner->id,
            'name' => 'Stand Sehat',
            'balance' => 0,
            'is_open' => true,
        ]);

        $menu = CanteenMenu::create([
            'canteen_stand_id' => $stand->id,
            'name' => 'Soto Ayam',
            'price' => 15000,
            'stock' => 5,
            'is_available' => true,
        ]);

        [$studentUser, $student, $wallet] = $this->createStudentWithWallet(balance: 50000, pin: '654321');
        $wallet->update(['daily_limit' => 20000]);

        // PIN salah -> ditolak, saldo & stok tetap
        $this->actingAs($studentUser)
            ->post(route('siswa.kantin.checkout'), [
                'canteen_stand_id' => $stand->id,
                'items' => [['menu_id' => $menu->id, 'quantity' => 1]],
                'pin' => '000000',
            ])
            ->assertSessionHasErrors('pin');

        $this->assertSame(50000.0, (float) $wallet->fresh()->balance);
        $this->assertSame(5, $menu->fresh()->stock);

        // Melebihi daily_limit (2 x 15.000 = 30.000 > 20.000) -> ditolak
        $this->actingAs($studentUser)
            ->post(route('siswa.kantin.checkout'), [
                'canteen_stand_id' => $stand->id,
                'items' => [['menu_id' => $menu->id, 'quantity' => 2]],
                'pin' => '654321',
            ])
            ->assertSessionHasErrors('pin');

        // Checkout valid 1 porsi (15.000 <= 20.000)
        $this->actingAs($studentUser)
            ->post(route('siswa.kantin.checkout'), [
                'canteen_stand_id' => $stand->id,
                'items' => [['menu_id' => $menu->id, 'quantity' => 1]],
                'pin' => '654321',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(35000.0, (float) $wallet->fresh()->balance);
        $this->assertSame(15000.0, (float) $stand->fresh()->balance);
        $this->assertSame(4, $menu->fresh()->stock);

        $order = CanteenOrder::where('student_id', $student->id)->firstOrFail();
        $this->assertSame('Menunggu Konfirmasi', $order->status);
        $this->assertDatabaseHas('canteen_order_items', [
            'canteen_order_id' => $order->id,
            'canteen_menu_id' => $menu->id,
            'quantity' => 1,
            'subtotal' => 15000,
        ]);
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'Pembayaran Kantin',
            'reference_id' => $order->id,
            'amount' => 15000,
        ]);
    }

    public function test_canteen_owner_can_validate_qr_pickup_and_cancel_refunds_wallet_and_stock(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('Pengelola Stand Kantin');

        $stand = CanteenStand::create([
            'user_id' => $owner->id,
            'name' => 'Stand Jujur',
            'balance' => 0,
            'is_open' => true,
        ]);

        $menu = CanteenMenu::create([
            'canteen_stand_id' => $stand->id,
            'name' => 'Roti Bakar',
            'price' => 8000,
            'stock' => 10,
            'is_available' => true,
        ]);

        [$studentUser, $student, $wallet] = $this->createStudentWithWallet(balance: 40000, pin: '123456');

        $this->actingAs($studentUser)->post(route('siswa.kantin.checkout'), [
            'canteen_stand_id' => $stand->id,
            'items' => [['menu_id' => $menu->id, 'quantity' => 2]],
            'pin' => '123456',
        ]);

        $order1 = CanteenOrder::where('student_id', $student->id)->firstOrFail();

        $this->actingAs($owner)
            ->post(route('kantin.scan.validate'), ['qr_code' => $order1->qr_code_pickup])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('Selesai', $order1->fresh()->status);

        // Pesanan ke-2 dibatalkan oleh pengelola -> refund saldo & stok kembali
        $this->actingAs($studentUser)->post(route('siswa.kantin.checkout'), [
            'canteen_stand_id' => $stand->id,
            'items' => [['menu_id' => $menu->id, 'quantity' => 1]],
            'pin' => '123456',
        ]);

        $order2 = CanteenOrder::where('student_id', $student->id)->latest('created_at')->where('id', '!=', $order1->id)->firstOrFail();

        $this->actingAs($owner)
            ->patch(route('kantin.orders.status', $order2), ['status' => 'Dibatalkan'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('Dibatalkan', $order2->fresh()->status);
        $this->assertSame(24000.0, (float) $wallet->fresh()->balance);
        $this->assertSame(16000.0, (float) $stand->fresh()->balance);
        $this->assertSame(8, $menu->fresh()->stock);
    }

    public function test_parent_can_update_own_child_daily_limit_and_cannot_update_other_child(): void
    {
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');

        $otherParent = User::factory()->create();
        $otherParent->assignRole('Orang Tua');

        [, $ownChild, $ownWallet] = $this->createStudentWithWallet(parent: $parent);
        [, $otherChild] = $this->createStudentWithWallet(parent: $otherParent);

        $this->actingAs($parent)
            ->post(route('ortu.wallet.limit'), [
                'student_id' => $ownChild->id,
                'daily_limit' => 25000,
            ])
            ->assertRedirect();

        $this->assertSame(25000.0, (float) $ownWallet->fresh()->daily_limit);

        $this->actingAs($parent)
            ->post(route('ortu.wallet.limit'), [
                'student_id' => $otherChild->id,
                'daily_limit' => 10000,
            ])
            ->assertForbidden();
    }

    public function test_bendahara_can_view_canteen_report_and_disabled_module_blocks_routes(): void
    {
        $bendahara = User::factory()->create();
        $bendahara->assignRole('Bendahara');

        $owner = User::factory()->create();
        $owner->assignRole('Pengelola Stand Kantin');

        $this->actingAs($bendahara)
            ->get(route('bendahara.kantin'))
            ->assertOk();

        app(ModuleService::class)->ensureSeeded();
        $kantinModule = Module::where('code', 'kantin')->firstOrFail();

        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');

        $this->actingAs($admin)
            ->patch(route('sistem.modules.toggle', $kantinModule))
            ->assertRedirect();

        $this->actingAs($owner)->get(route('kantin.dashboard'))->assertForbidden();
        $this->actingAs($bendahara)->get(route('bendahara.kantin'))->assertForbidden();
    }
}
