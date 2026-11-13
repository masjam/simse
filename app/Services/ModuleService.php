<?php

namespace App\Services;

use App\Models\Module;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ModuleService
{
    private const CACHE_KEY = 'simse.modules.active_map';

    /**
     * @return list<array{code: string, name: string, description: string, is_active: bool}>
     */
    public static function defaults(): array
    {
        return [
            ['code' => 'akademik', 'name' => 'SIAKAD & Akademik', 'description' => 'Sistem Informasi Akademik, Jadwal, dan e-Raport', 'is_active' => true],
            ['code' => 'lms', 'name' => 'E-Learning (LMS)', 'description' => 'Materi Belajar, Materi Mingguan, dan Tugas Online', 'is_active' => true],
            ['code' => 'cbt', 'name' => 'Ujian Online (CBT)', 'description' => 'Ujian Online dengan Pengawasan Anti-Kecurangan', 'is_active' => true],
            ['code' => 'bk', 'name' => 'Bimbingan Konseling', 'description' => 'Pencatatan Poin Pelanggaran, SP, dan Konseling', 'is_active' => true],
            ['code' => 'absensi', 'name' => 'Absensi Digital', 'description' => 'Presensi Kelas, QR Code, dan Notifikasi Wali', 'is_active' => true],
            ['code' => 'keuangan', 'name' => 'Keuangan & SPP', 'description' => 'Kasir, Tagihan SPP, dan Dompet Digital', 'is_active' => true],
            ['code' => 'kantin', 'name' => 'E-Kantin Digital', 'description' => 'Dompet Digital dan Transaksi Stand Kantin', 'is_active' => true],
            ['code' => 'payroll', 'name' => 'Payroll Pegawai', 'description' => 'Penggajian, Slip Gaji, dan Tunjangan Pegawai', 'is_active' => true],
            ['code' => 'ppdb', 'name' => 'PPDB Online', 'description' => 'Penerimaan Peserta Didik Baru', 'is_active' => true],
            ['code' => 'perpustakaan', 'name' => 'Perpustakaan Digital', 'description' => 'Katalog Buku dan Peminjaman', 'is_active' => true],
        ];
    }

    public function ensureSeeded(): void
    {
        if (! Schema::hasTable('modules')) {
            return;
        }

        if (Module::query()->exists()) {
            return;
        }

        foreach (self::defaults() as $item) {
            Module::firstOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'is_active' => $item['is_active'],
                ]
            );
        }

        $this->clearCache();
    }

    /**
     * @return Collection<int, Module>
     */
    public function all(): Collection
    {
        $this->ensureSeeded();

        return Module::query()->orderBy('name')->get();
    }

    /**
     * @return array<string, bool>
     */
    public function activeMap(): array
    {
        if (! Schema::hasTable('modules')) {
            return collect(self::defaults())->pluck('is_active', 'code')->all();
        }

        $this->ensureSeeded();

        return Cache::remember(self::CACHE_KEY, 300, function (): array {
            return Module::query()
                ->pluck('is_active', 'code')
                ->map(fn (mixed $val): bool => (bool) $val)
                ->all();
        });
    }

    public function isActive(string $code): bool
    {
        $map = $this->activeMap();

        return $map[$code] ?? true;
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
