<?php

namespace App\Http\Middleware;

use App\Models\KioskDevice;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureKioskMacAllowed
{
    public function handle(Request $request, Closure $next): Response
    {
        $rawMac = $request->header('X-Device-Mac')
            ?: $request->query('mac')
            ?: $request->session()->get('kiosk_device_mac');

        if (! is_string($rawMac) || trim($rawMac) === '') {
            return $this->denyAccess($request, null);
        }

        $normalizedMac = KioskDevice::normalizeMac($rawMac);

        $device = KioskDevice::where('mac_address', $normalizedMac)
            ->where('is_active', true)
            ->first();

        if (! $device) {
            $request->session()->forget(['kiosk_device_mac', 'kiosk_device_id']);

            return $this->denyAccess($request, $normalizedMac);
        }

        $request->session()->put('kiosk_device_mac', $device->mac_address);
        $request->session()->put('kiosk_device_id', $device->id);
        $device->update(['last_seen_at' => now()]);
        $request->attributes->set('kiosk_device', $device);

        return $next($request);
    }

    private function denyAccess(Request $request, ?string $attemptedMac): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Perangkat kiosk tidak terdaftar atau dinonaktifkan oleh Admin.',
                'attempted_mac' => $attemptedMac,
            ], 403);
        }

        return Inertia::render('Kiosk/Locked', [
            'attemptedMac' => $attemptedMac,
        ])->toResponse($request)->setStatusCode(403);
    }
}
