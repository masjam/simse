<?php

namespace Database\Seeders;

use App\Models\CanteenMenu;
use App\Models\CanteenStand;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CanteenDemoSeeder extends Seeder
{
    public function run(): void
    {
        $kantinRole = Role::firstOrCreate(['name' => 'Pengelola Stand Kantin', 'guard_name' => 'web']);

        $owner = User::firstOrCreate(
            ['email' => 'kantin@sekolah.com'],
            [
                'name' => 'Ibu Siti (Kantin Sehat)',
                'password' => Hash::make('password'),
            ]
        );
        if (! $owner->hasRole($kantinRole->name)) {
            $owner->assignRole($kantinRole);
        }

        $stand = CanteenStand::firstOrCreate(
            ['user_id' => $owner->id],
            [
                'name' => 'Stand Ibu Siti (Kantin Sehat)',
                'description' => 'Menu sehat bebas MSG dan pengawet untuk siswa SD Muhammadiyah Al Mujahidin',
                'balance' => 0,
                'is_open' => true,
            ]
        );

        $menus = [
            ['name' => 'Nasi Goreng Telur', 'price' => 10000, 'stock' => 25, 'description' => 'Nasi goreng hangat dengan telur dadar'],
            ['name' => 'Soto Ayam Kampung', 'price' => 12000, 'stock' => 20, 'description' => 'Soto ayam segar dengan nasi dan suwiran ayam'],
            ['name' => 'Roti Bakar Keju Coklat', 'price' => 6000, 'stock' => 30, 'description' => 'Roti gandum panggang isi keju dan coklat'],
            ['name' => 'Jus Jeruk Segar', 'price' => 5000, 'stock' => 40, 'description' => 'Perasan jeruk asli tanpa pemanis buatan'],
        ];

        foreach ($menus as $menu) {
            CanteenMenu::firstOrCreate(
                [
                    'canteen_stand_id' => $stand->id,
                    'name' => $menu['name'],
                ],
                [
                    'price' => $menu['price'],
                    'stock' => $menu['stock'],
                    'description' => $menu['description'],
                    'is_available' => true,
                ]
            );
        }

        Student::query()->each(function (Student $student): void {
            if (empty($student->rfid_uid)) {
                $suffix = strtoupper(substr(str_replace('-', '', $student->id), -10));
                $student->update([
                    'rfid_uid' => 'RFID-'.$suffix,
                ]);
            }

            $wallet = Wallet::firstOrCreate(
                ['student_id' => $student->id],
                ['balance' => 100000, 'pin' => '123456']
            );

            if (empty($wallet->pin)) {
                $wallet->update(['pin' => '123456']);
            }
        });
    }
}
