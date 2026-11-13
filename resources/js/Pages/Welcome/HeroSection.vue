<script setup>
import { Link } from '@inertiajs/vue3';
import AppButton from '@/Components/ui/AppButton.vue';
import AppCard from '@/Components/ui/AppCard.vue';

defineProps({
    canRegister: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <section class="py-12 sm:py-20 bg-surface-sunken border-b border-line">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                <!-- Hero Content -->
                <div class="lg:col-span-7 text-center lg:text-left space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-50 border border-brand-200 text-brand-700 text-xs font-semibold">
                        <span class="w-2 h-2 rounded-full bg-accent-500 animate-pulse"></span>
                        <span>Sekolah Dasar Islam Terpadu Rujukan Unggulan Gunungkidul</span>
                        <span>•</span>
                        <span>T.A 2026/2027</span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl font-extrabold text-ink tracking-tight leading-tight">
                        Mewujudkan Generasi <br class="hidden sm:inline" />
                        <span class="text-brand-700">"Belia Mendunia"</span> yang Cerdas & Shalih
                    </h1>

                    <p class="text-sm sm:text-base text-ink-muted max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                        Selamat datang di <strong>SD Muhammadiyah Al Mujahidin Wonosari</strong>. Kami menghadirkan pendidikan dasar Islam terpadu yang memadukan kedalaman Al-Qur’an (Tahfidz), penanaman karakter akhlakul karimah, wawasan internasional (International Exchange), serta modernitas tata kelola sekolah cerdas berbasis <strong>SIMSE</strong>.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3">
                        <Link v-if="canRegister" :href="route('ppdb.form')">
                            <AppButton variant="primary" size="lg" class="shadow-card">
                                Pendaftaran Siswa Baru (Inden) →
                            </AppButton>
                        </Link>
                        <a href="#profil">
                            <AppButton variant="secondary" size="lg">
                                Sambutan Kepala Sekolah ↓
                            </AppButton>
                        </a>
                    </div>

                    <div class="pt-4 border-t border-line flex flex-wrap items-center justify-center lg:justify-start gap-4 text-xs text-ink-muted">
                        <div class="flex items-center gap-1.5">
                            <span class="text-brand-700 font-bold">✓</span>
                            <span>Terakreditasi 'A' (Unggul) • NPSN: 20402170</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-accent-600 font-bold">★</span>
                            <span>Sekolah Ramah Anak Bebas Gawai di Kelas</span>
                        </div>
                    </div>
                </div>

                <!-- Visual Card -->
                <div class="lg:col-span-5">
                    <AppCard class="p-2 sm:p-3 shadow-card" padding="none">
                        <div class="relative rounded-control overflow-hidden aspect-[16/11]">
                            <img
                                src="https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=1000&q=80"
                                alt="Aktivitas Siswa SD Muhammadiyah Al Mujahidin"
                                class="w-full h-full object-cover"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                            <div class="absolute bottom-3 left-3 right-3 p-3 rounded-control bg-surface/90 backdrop-blur-sm border border-line text-xs">
                                <p class="text-ink-muted">Kampus Gadungsari Wonosari</p>
                                <p class="font-bold text-ink">Full Day School Ramah Bakat</p>
                            </div>
                        </div>
                    </AppCard>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="mt-14 pt-8 border-t border-line grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <AppCard class="p-4">
                    <div class="text-2xl sm:text-3xl font-extrabold text-brand-700 font-numeric">900+</div>
                    <div class="text-xs font-semibold text-ink mt-1">Siswa-Siswi Berprestasi</div>
                    <div class="text-[11px] text-ink-muted">Kelas 1 s.d. 6 • Sistem Inden</div>
                </AppCard>
                <AppCard class="p-4">
                    <div class="text-2xl sm:text-3xl font-extrabold text-accent-600 font-numeric">60+</div>
                    <div class="text-xs font-semibold text-ink mt-1">Ustadz & Ustadzah</div>
                    <div class="text-[11px] text-ink-muted">Pendidik Berdedikasi</div>
                </AppCard>
                <AppCard class="p-4">
                    <div class="text-2xl sm:text-3xl font-extrabold text-brand-700 font-numeric">25+</div>
                    <div class="text-xs font-semibold text-ink mt-1">Ekstrakurikuler Pilihan</div>
                    <div class="text-[11px] text-ink-muted">Polcil, HW, Tapak Suci</div>
                </AppCard>
                <AppCard class="p-4">
                    <div class="text-2xl sm:text-3xl font-extrabold text-accent-600 font-numeric">100%</div>
                    <div class="text-xs font-semibold text-ink mt-1">Terkoneksi SIMSE</div>
                    <div class="text-[11px] text-ink-muted">Presensi WA & e-Kantin</div>
                </AppCard>
            </div>
        </div>
    </section>
</template>

