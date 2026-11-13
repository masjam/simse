<?php

namespace App\Http\Controllers;

use App\Http\Requests\System\StoreKioskDeviceRequest;
use App\Models\KioskDevice;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class KioskDeviceController extends Controller
{
    public function index(): Response
    {
        $devices = KioskDevice::orderBy('name')
            ->get()
            ->map(fn (KioskDevice $device): array => [
                'id' => $device->id,
                'name' => $device->name,
                'mac_address' => $device->mac_address,
                'location' => $device->location ?? '-',
                'is_active' => $device->is_active,
                'last_seen_at' => $device->last_seen_at?->format('d M Y, H:i') ?? 'Belum pernah terhubung',
                'kiosk_url' => route('kiosk.presensi', ['mac' => $device->mac_address]),
            ]);

        return Inertia::render('System/KioskDevices', [
            'devices' => $devices,
        ]);
    }

    public function store(StoreKioskDeviceRequest $request): RedirectResponse
    {
        KioskDevice::create([
            ...$request->validated(),
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Perangkat Kiosk (MAC Address) berhasil didaftarkan.');
    }

    public function toggle(KioskDevice $kioskDevice): RedirectResponse
    {
        $kioskDevice->update([
            'is_active' => ! $kioskDevice->is_active,
        ]);

        return redirect()->back()->with(
            'success',
            $kioskDevice->is_active
                ? "Kiosk {$kioskDevice->name} diaktifkan."
                : "Kiosk {$kioskDevice->name} dinonaktifkan."
        );
    }

    public function destroy(KioskDevice $kioskDevice): RedirectResponse
    {
        $kioskDevice->delete();

        return redirect()->back()->with('success', 'Perangkat Kiosk berhasil dihapus.');
    }
}
