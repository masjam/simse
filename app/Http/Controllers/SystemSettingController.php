<?php

namespace App\Http\Controllers;

use App\Http\Requests\System\SendAnnouncementRequest;
use App\Http\Requests\System\UpdateSystemSettingsRequest;
use App\Http\Requests\System\UpdateWaGatewayRequest;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Models\Module;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WhatsappLog;
use App\Notifications\SimseNotification;
use App\Services\ModuleService;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class SystemSettingController extends Controller
{
    public function __construct(
        private readonly ModuleService $moduleService,
        private readonly WhatsAppService $whatsAppService,
    ) {}

    public function settings(): Response
    {
        $modules = $this->moduleService->all()->map(fn (Module $module): array => [
            'id' => $module->id,
            'code' => $module->code,
            'name' => $module->name,
            'description' => $module->description,
            'is_active' => (bool) $module->is_active,
        ])->values();

        return Inertia::render('System/Settings', [
            'settings' => [
                'school_name' => (string) SystemSetting::getValue('school_name', 'SD Muhammadiyah Al Mujahidin Wonosari'),
                'school_address' => (string) SystemSetting::getValue('school_address', 'Jl. KH Agus Salim No. 35, Wonosari, Gunungkidul'),
                'school_phone' => (string) SystemSetting::getValue('school_phone', '(0274) 391234'),
                'school_email' => (string) SystemSetting::getValue('school_email', 'info@sdmujahidin-wns.sch.id'),
                'active_semester' => (string) SystemSetting::getValue('active_semester', 'Ganjil 2026/2027'),
                'school_latitude' => (string) SystemSetting::getValue('school_latitude', '-7.9664'),
                'school_longitude' => (string) SystemSetting::getValue('school_longitude', '110.6035'),
                'school_radius_meters' => (string) SystemSetting::getValue('school_radius_meters', '150'),
            ],
            'modules' => $modules,
        ]);
    }

    public function updateSettings(UpdateSystemSettingsRequest $request): RedirectResponse
    {
        foreach ($request->validated() as $key => $value) {
            SystemSetting::setValue($key, $value !== null ? (string) $value : null);
        }

        return back()->with('success', 'Pengaturan sistem sekolah berhasil diperbarui.');
    }

    public function toggleModule(Module $module): RedirectResponse
    {
        $module->update([
            'is_active' => ! $module->is_active,
        ]);

        $this->moduleService->clearCache();

        $statusLabel = $module->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Modul {$module->name} berhasil {$statusLabel}.");
    }

    public function waGateway(): Response
    {
        $logs = WhatsappLog::query()
            ->latest()
            ->take(50)
            ->get()
            ->map(fn (WhatsappLog $log): array => [
                'id' => $log->id,
                'recipient_name' => $log->recipient_name ?? '-',
                'phone_number' => $log->phone_number,
                'message' => $log->message,
                'status' => $log->status,
                'response_log' => $log->response_log,
                'created_at' => $log->created_at?->format('d M Y, H:i') ?? '-',
            ])
            ->values();

        return Inertia::render('System/WaGateway', [
            'config' => [
                'wa_driver' => (string) SystemSetting::getValue('wa_driver', config('services.whatsapp.driver', 'log')),
                'wa_is_active' => (string) SystemSetting::getValue('wa_is_active', '1') !== '0',
                'wa_fonnte_token' => (string) SystemSetting::getValue('wa_fonnte_token', config('services.whatsapp.fonnte.token', '')),
                'wa_fonnte_endpoint' => (string) SystemSetting::getValue('wa_fonnte_endpoint', config('services.whatsapp.fonnte.endpoint', 'https://api.fonnte.com/send')),
                'wa_wablas_token' => (string) SystemSetting::getValue('wa_wablas_token', config('services.whatsapp.wablas.token', '')),
                'wa_wablas_endpoint' => (string) SystemSetting::getValue('wa_wablas_endpoint', config('services.whatsapp.wablas.endpoint', 'https://solo.wablas.com/api/send-message')),
            ],
            'stats' => [
                'sent' => WhatsappLog::where('status', 'Terkirim')->count(),
                'pending' => WhatsappLog::where('status', 'Pending')->count(),
                'failed' => WhatsappLog::where('status', 'Gagal')->count(),
            ],
            'logs' => $logs,
        ]);
    }

    public function updateWaGateway(UpdateWaGatewayRequest $request): RedirectResponse
    {
        $data = $request->validated();

        SystemSetting::setValue('wa_driver', $data['wa_driver'], 'Driver aktif WhatsApp Gateway');
        SystemSetting::setValue('wa_is_active', $data['wa_is_active'] ? '1' : '0', 'Status aktif pengiriman WhatsApp');
        SystemSetting::setValue('wa_fonnte_token', $data['wa_fonnte_token'] ?? '', 'API Token Fonnte');
        SystemSetting::setValue('wa_fonnte_endpoint', $data['wa_fonnte_endpoint'], 'Endpoint API Fonnte');
        SystemSetting::setValue('wa_wablas_token', $data['wa_wablas_token'] ?? '', 'API Token Wablas');
        SystemSetting::setValue('wa_wablas_endpoint', $data['wa_wablas_endpoint'], 'Endpoint API Wablas');

        return back()->with('success', 'Konfigurasi WhatsApp Gateway berhasil disimpan.');
    }

    public function sendAnnouncement(SendAnnouncementRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $targetRole = $data['target_role'];

        $users = $targetRole === 'all'
            ? User::with('student')->get()
            : User::with('student')->role($targetRole)->get();

        if ($users->isEmpty()) {
            return back()->with('error', 'Tidak ada pengguna yang sesuai dengan target pengumuman.');
        }

        Notification::send(
            $users,
            new SimseNotification(
                title: $data['title'],
                message: $data['message'],
                category: 'announcement',
                url: route('notifications.index'),
            )
        );

        $waQueuedCount = 0;

        if ($data['send_whatsapp']) {
            $waMessage = "*[PENGUMUMAN SIMSE]*\n*{$data['title']}*\n\n{$data['message']}";

            foreach ($users as $user) {
                $phone = $user->phone ?: $user->student?->no_telp_ortu;

                if (! $phone) {
                    continue;
                }

                if ($this->whatsAppService->queue($phone, $waMessage, $user->name)) {
                    $waQueuedCount++;
                }
            }
        }

        return back()->with('success', "Pengumuman dikirim ke {$users->count()} akun (antrean WA: {$waQueuedCount} penerima).");
    }

    public function retryWaLog(WhatsappLog $whatsappLog): RedirectResponse
    {
        $whatsappLog->update([
            'status' => 'Pending',
            'response_log' => 'Dijadwalkan kirim ulang oleh Administrator.',
        ]);

        SendWhatsAppNotificationJob::dispatch($whatsappLog->id);

        return back()->with('success', 'Pesan WhatsApp dijadwalkan untuk dikirim ulang.');
    }
}
