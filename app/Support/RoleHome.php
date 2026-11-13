<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\RedirectResponse;

class RoleHome
{
    /**
     * Redirect user to their corresponding role portal for a specific module or general home.
     */
    public static function for(?User $user, ?string $module = null): RedirectResponse
    {
        if (! $user) {
            return redirect()->route('login');
        }

        $targetUrl = match ($module) {
            'lms' => self::forLms($user),
            'cbt' => self::forCbt($user),
            'perpustakaan' => self::forPerpustakaan($user),
            'spp' => self::forSpp($user),
            'dompet' => self::forDompet($user),
            'kantin' => self::forKantin($user),
            'payroll' => self::forPayroll($user),
            'bk' => self::forBk($user),
            'ppdb' => self::forPpdb($user),
            default => self::defaultFor($user),
        };

        return redirect()->to($targetUrl);
    }

    public static function defaultFor(User $user): string
    {
        if ($user->hasRole('Super Admin')) {
            return route('admin.dashboard');
        } elseif ($user->hasAnyRole(['Guru Pengajar', 'Guru BK'])) {
            return route('guru.dashboard');
        } elseif ($user->hasRole('Bendahara')) {
            return route('bendahara.dashboard');
        } elseif ($user->hasRole('Pengelola Stand Kantin')) {
            return route('kantin.dashboard');
        } elseif ($user->hasRole('Staf Sekolah')) {
            return route('staf.dashboard');
        } elseif ($user->hasRole('Siswa')) {
            return route('siswa.dashboard');
        } elseif ($user->hasRole('Orang Tua')) {
            return route('ortu.dashboard');
        }

        return route('dashboard');
    }

    private static function forLms(User $user): string
    {
        if ($user->hasRole('Siswa')) {
            return route('siswa.lms');
        }
        if ($user->hasAnyRole(['Guru Pengajar', 'Guru BK'])) {
            return route('guru.lms');
        }
        if ($user->hasRole('Orang Tua')) {
            return route('ortu.dashboard');
        }

        return self::defaultFor($user);
    }

    private static function forCbt(User $user): string
    {
        if ($user->hasRole('Siswa')) {
            return route('siswa.cbt');
        }
        if ($user->hasAnyRole(['Guru Pengajar', 'Guru BK'])) {
            return route('guru.cbt');
        }

        return self::defaultFor($user);
    }

    private static function forPerpustakaan(User $user): string
    {
        if ($user->hasAnyRole(['Staf Sekolah', 'Super Admin'])) {
            return route('staf.perpustakaan');
        }
        if ($user->hasAnyRole(['Guru Pengajar', 'Guru BK'])) {
            return route('guru.perpustakaan');
        }

        return self::defaultFor($user);
    }

    private static function forSpp(User $user): string
    {
        if ($user->hasAnyRole(['Bendahara', 'Super Admin'])) {
            return route('bendahara.spp');
        }
        if ($user->hasRole('Orang Tua')) {
            return route('ortu.dashboard');
        }

        return self::defaultFor($user);
    }

    private static function forDompet(User $user): string
    {
        if ($user->hasAnyRole(['Bendahara', 'Super Admin'])) {
            return route('bendahara.dompet');
        }
        if ($user->hasRole('Siswa')) {
            return route('siswa.kantin');
        }
        if ($user->hasRole('Orang Tua')) {
            return route('ortu.dashboard');
        }

        return self::defaultFor($user);
    }

    private static function forKantin(User $user): string
    {
        if ($user->hasRole('Pengelola Stand Kantin')) {
            return route('kantin.menus.index');
        }
        if ($user->hasRole('Siswa')) {
            return route('siswa.kantin');
        }
        if ($user->hasAnyRole(['Bendahara', 'Super Admin'])) {
            return route('bendahara.kantin.report');
        }

        return self::defaultFor($user);
    }

    private static function forPayroll(User $user): string
    {
        if ($user->hasAnyRole(['Bendahara', 'Super Admin'])) {
            return route('bendahara.payroll');
        }
        if ($user->hasRole('Staf Sekolah')) {
            return route('staf.slip-gaji');
        }
        if ($user->hasAnyRole(['Guru Pengajar', 'Guru BK'])) {
            return route('guru.slip-gaji');
        }

        return self::defaultFor($user);
    }

    private static function forBk(User $user): string
    {
        if ($user->hasAnyRole(['Guru BK', 'Guru Pengajar', 'Super Admin'])) {
            return route('guru.bk');
        }
        if ($user->hasRole('Orang Tua')) {
            return route('ortu.dashboard');
        }

        return self::defaultFor($user);
    }

    private static function forPpdb(User $user): string
    {
        if ($user->hasAnyRole(['Super Admin', 'Operator', 'Admin Tata Usaha'])) {
            return route('admin.ppdb.index');
        }

        return route('ppdb.form');
    }
}
