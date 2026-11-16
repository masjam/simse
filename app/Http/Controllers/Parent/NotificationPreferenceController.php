<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\ParentNotificationPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    /**
     * Perbarui preferensi notifikasi orang tua (WhatsApp / In-App per jenis pengumuman).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'channel_wa' => 'required|boolean',
            'channel_in_app' => 'required|boolean',
            'notify_attendance' => 'required|boolean',
            'notify_finance' => 'required|boolean',
            'notify_grades' => 'required|boolean',
            'notify_discipline' => 'required|boolean',
        ]);

        ParentNotificationPreference::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return back()->with('success', 'Preferensi notifikasi berhasil diperbarui.');
    }
}
