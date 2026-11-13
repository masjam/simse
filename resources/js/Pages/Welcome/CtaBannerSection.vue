<script setup>
import { Link } from '@inertiajs/vue3';
import AppButton from '@/Components/ui/AppButton.vue';

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <section class="py-16 sm:py-20 bg-surface-sunken border-b border-line text-center">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <span class="inline-block px-3.5 py-1 rounded-full bg-brand-50 text-brand-700 text-xs font-bold uppercase tracking-wider border border-brand-200">
                Sistem Digital SIMSE Al Mujahidin
            </span>

            <h2 class="text-2xl sm:text-4xl font-extrabold text-ink">
                Kemitraan Nyata Sekolah & Orang Tua
            </h2>

            <p class="max-w-2xl mx-auto text-xs sm:text-sm text-ink-muted leading-relaxed">
                Presensi kedatangan ananda dikirim otomatis ke WhatsApp orang tua. Pantau mutaba’ah hafalan tahfidz Al-Qur'an, capaian nilai berkala, dan limit jajan sehat di e-Kantin melalui portal digital SIMSE.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                <Link v-if="canLogin" :href="route('login')">
                    <AppButton variant="primary" size="lg" class="shadow-card">
                        Masuk Portal SIMSE →
                    </AppButton>
                </Link>
                <Link :href="route('ppdb.form')">
                    <AppButton variant="secondary" size="lg">
                        Pendaftaran Inden / PPDB
                    </AppButton>
                </Link>
            </div>
        </div>
    </section>
</template>

