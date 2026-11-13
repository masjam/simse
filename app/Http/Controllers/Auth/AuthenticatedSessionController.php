<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request, ?string $role = null): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'initialRole' => $role ?? $request->query('role', 'guru'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, ?string $role = null): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        $selectedRole = $role ?? $request->input('selected_role');

        // Validasi kesesuaian jika user memilih jalur login tertentu
        if ($selectedRole) {
            $isAuthorized = match ($selectedRole) {
                'admin' => $user->hasAnyRole(['Super Admin', 'Operator', 'Admin Tata Usaha']),
                'guru' => $user->hasAnyRole(['Guru Pengajar', 'Guru BK', 'Super Admin']),
                'bk' => $user->hasAnyRole(['Guru BK', 'Super Admin']),
                'staf' => $user->hasAnyRole(['Bendahara', 'Staf Sekolah', 'Admin Tata Usaha', 'Pengelola Stand Kantin', 'Super Admin']),
                'bendahara' => $user->hasAnyRole(['Bendahara', 'Super Admin']),
                'kantin' => $user->hasAnyRole(['Pengelola Stand Kantin', 'Super Admin']),
                'siswa' => $user->hasAnyRole(['Siswa', 'Calon Siswa Baru', 'Super Admin']),
                'ortu' => $user->hasAnyRole(['Orang Tua', 'Super Admin']),
                'ppdb' => $user->hasAnyRole(['Calon Siswa Baru', 'Super Admin']),
                default => true,
            };

            if (! $isAuthorized) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $roleLabel = match ($selectedRole) {
                    'admin' => 'Administrator',
                    'guru' => 'Guru & Pendidik',
                    'bk' => 'Guru BK',
                    'staf' => 'Staf & Keuangan',
                    'bendahara' => 'Bendahara',
                    'kantin' => 'Pengelola Stand Kantin',
                    'siswa' => 'Siswa',
                    'ortu' => 'Orang Tua / Wali Murid',
                    'ppdb' => 'Calon Siswa Baru (PPDB)',
                    default => 'ini',
                };

                return back()->withErrors([
                    'email' => "Akun Anda tidak memiliki hak akses pada Jalur Portal $roleLabel. Silakan pilih jalur login yang sesuai.",
                ]);
            }
        }

        if ($user->hasRole('Super Admin')) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        } elseif ($user->hasAnyRole(['Guru Pengajar', 'Guru BK'])) {
            return redirect()->intended(route('guru.dashboard', absolute: false));
        } elseif ($user->hasRole('Bendahara')) {
            return redirect()->intended(route('bendahara.dashboard', absolute: false));
        } elseif ($user->hasRole('Pengelola Stand Kantin')) {
            return redirect()->intended(route('kantin.dashboard', absolute: false));
        } elseif ($user->hasRole('Staf Sekolah')) {
            return redirect()->intended(route('staf.dashboard', absolute: false));
        } elseif ($user->hasRole('Siswa')) {
            return redirect()->intended(route('siswa.dashboard', absolute: false));
        } elseif ($user->hasRole('Orang Tua')) {
            return redirect()->intended(route('ortu.dashboard', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
