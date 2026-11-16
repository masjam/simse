<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';

const props = defineProps({
    isValid: {
        type: Boolean,
        default: false,
    },
    token: {
        type: String,
        required: true,
    },
    document: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <Head title="Verifikasi Keaslian Dokumen - SD Muhammadiyah Al Mujahidin" />

    <PortalShell variant="bare">
        <div class="min-h-screen bg-surface-sunken flex flex-col justify-between p-4 sm:p-6 md:p-12">
            <!-- Header Brand -->
            <header class="max-w-2xl mx-auto w-full text-center mb-6">
                <div class="inline-flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-brand-50 border border-brand-200 flex items-center justify-center text-brand-700 font-bold text-xl">
                        M
                    </div>
                    <div class="text-left">
                        <h1 class="text-base sm:text-lg font-bold text-ink">SD Muhammadiyah Al Mujahidin</h1>
                        <p class="text-xs text-ink-muted">Sistem Verifikasi Dokumen & Ijazah Elektronik (SIMSE)</p>
                    </div>
                </div>
            </header>

            <!-- Main Content Card -->
            <main class="max-w-xl mx-auto w-full">
                <!-- Valid State -->
                <AppCard v-if="document && isValid" class="space-y-6 border-status-success-line">
                    <div class="flex items-center gap-3 p-4 rounded-control bg-status-success-bg text-status-success-fg border border-status-success-line">
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-lg flex-shrink-0 shadow-sm">
                            ✅
                        </div>
                        <div>
                            <h2 class="text-base font-bold">DOKUMEN RESMI DAN SAH</h2>
                            <p class="text-xs opacity-90">Dokumen terdaftar resmi di basis data SD Muhammadiyah Al Mujahidin Wonosari.</p>
                        </div>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div class="flex flex-col sm:flex-row sm:justify-between py-2 border-b border-line gap-1">
                            <span class="text-xs text-ink-muted">Jenis Dokumen</span>
                            <span class="font-semibold text-ink">{{ document.document_type }}</span>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:justify-between py-2 border-b border-line gap-1">
                            <span class="text-xs text-ink-muted">Penerima Dokumen</span>
                            <span class="font-bold text-brand-700 tracking-wide">{{ document.recipient_masked }}</span>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:justify-between py-2 border-b border-line gap-1">
                            <span class="text-xs text-ink-muted">Judul / Perihal</span>
                            <span class="text-ink font-medium">{{ document.title }}</span>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:justify-between py-2 border-b border-line gap-1">
                            <span class="text-xs text-ink-muted">Waktu Penerbitan</span>
                            <span class="text-ink">{{ document.issued_at }} WIB</span>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:justify-between py-2 border-b border-line gap-1">
                            <span class="text-xs text-ink-muted">Diterbitkan Oleh</span>
                            <span class="text-ink">{{ document.issuer_name }}</span>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:justify-between py-2 gap-1 text-xs">
                            <span class="text-ink-muted">Hash Integritas SHA-256</span>
                            <span class="font-mono text-ink-muted truncate">{{ document.content_hash_short }}</span>
                        </div>
                    </div>

                    <div class="p-3 rounded-control bg-surface-sunken text-xs text-ink-muted leading-relaxed">
                        🔒 Informasi penerima disamarkan sebagian untuk melindungi privasi data pribadi siswa dan orang tua. Dokumen ini dilindungi tanda tangan elektronik sekolah.
                    </div>
                </AppCard>

                <!-- Revoked State -->
                <AppCard v-else-if="document && document.is_revoked" class="space-y-6 border-status-danger-line">
                    <div class="flex items-center gap-3 p-4 rounded-control bg-status-danger-bg text-status-danger-fg border border-status-danger-line">
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-lg flex-shrink-0 shadow-sm">
                            ⚠️
                        </div>
                        <div>
                            <h2 class="text-base font-bold">DOKUMEN TELAH DICABUT</h2>
                            <p class="text-xs opacity-90">Dokumen ini pernah diterbitkan namun telah ditarik/dicabut oleh pihak sekolah.</p>
                        </div>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between py-2 border-b border-line">
                            <span class="text-xs text-ink-muted">Jenis Dokumen</span>
                            <span class="font-bold text-ink">{{ document.document_type }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-line">
                            <span class="text-xs text-ink-muted">Waktu Pencabutan</span>
                            <span class="text-ink">{{ document.revoked_at }} WIB</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-line">
                            <span class="text-xs text-ink-muted">Alasan Pencabutan</span>
                            <span class="text-status-danger-fg font-medium">{{ document.revoked_reason || 'Revisi dokumen atau pembatalan administratif.' }}</span>
                        </div>
                    </div>
                </AppCard>

                <!-- Invalid / Not Found State -->
                <AppCard v-else class="text-center py-6">
                    <EmptyState
                        title="Dokumen Tidak Ditemukan"
                        message="Kode verifikasi tidak terdaftar pada sistem SIMSE SD Muhammadiyah Al Mujahidin. Pastikan Anda memindai kode QR dari dokumen resmi asli."
                    >
                        <template #actions>
                            <Link :href="route('home')">
                                <AppButton variant="secondary" size="sm">
                                    Kembali ke Beranda
                                </AppButton>
                            </Link>
                        </template>
                    </EmptyState>
                </AppCard>
            </main>

            <!-- Footer -->
            <footer class="text-center text-xs text-ink-muted mt-8">
                &copy; 2026 SD Muhammadiyah Al Mujahidin Wonosari. Seluruh Hak Cipta Dilindungi.
            </footer>
        </div>
    </PortalShell>
</template>

