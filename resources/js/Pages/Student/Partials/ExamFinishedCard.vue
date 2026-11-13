<script setup>
import { Link } from '@inertiajs/vue3';
import AppButton from '@/Components/ui/AppButton.vue';

defineProps({
    exam: { type: Object, required: true },
    attempt: { type: Object, required: true },
});
</script>

<template>
    <div class="bg-surface rounded-card border border-line p-6 sm:p-8 shadow-card text-center space-y-4 max-w-xl mx-auto my-6">
        <div class="w-16 h-16 mx-auto rounded-full bg-status-success-bg text-status-success-fg flex items-center justify-center font-black text-2xl border border-status-success-line">
            ✓
        </div>
        <h2 class="text-xl font-black text-ink">Ujian Telah Diselesaikan</h2>
        <p class="text-xs sm:text-sm text-ink-muted">
            Dikumpulkan pada {{ attempt.finished_at }}
            <span v-if="attempt.is_forced_submit" class="text-status-danger-fg font-bold block mt-1">
                (Pengumpulan Otomatis oleh Sistem)
            </span>
        </p>

        <div class="grid grid-cols-2 gap-3 pt-2">
            <div class="p-4 rounded-control bg-surface-raised border border-line">
                <p class="text-xs text-ink-muted font-semibold">Nilai PG &amp; Benar-Salah</p>
                <p class="text-3xl font-black text-brand-600 mt-1 font-mono">{{ attempt.score ?? 0 }}</p>
            </div>
            <div class="p-4 rounded-control bg-surface-raised border border-line">
                <p class="text-xs text-ink-muted font-semibold">Catatan Pelanggaran</p>
                <p class="text-3xl font-black text-ink mt-1 font-mono">
                    {{ attempt.violations_count }} / {{ exam.max_violations }}
                </p>
            </div>
        </div>

        <p v-if="exam.has_essay" class="text-xs text-status-warning-fg bg-status-warning-bg border border-status-warning-line rounded-control p-3">
            Ujian ini memiliki soal Esai. Nilai total akhir akan disesuaikan setelah diperiksa dan dikoreksi oleh guru mata pelajaran.
        </p>

        <div class="pt-2">
            <Link :href="route('siswa.cbt')">
                <AppButton variant="primary">
                    Kembali ke Daftar Ujian
                </AppButton>
            </Link>
        </div>
    </div>
</template>

