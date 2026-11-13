<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppButton from '@/Components/ui/AppButton.vue';

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
});

const mobileMenuOpen = ref(false);
</script>

<template>
    <!-- TOP STRIP -->
    <div class="bg-surface-sunken border-b border-line text-xs py-2 px-4 text-ink-muted">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-control text-[11px] font-bold bg-accent-500 text-white uppercase tracking-wider">
                    PPDB 2026/2027
                </span>
                <span class="truncate">Penerimaan Siswa Baru SD Muhammadiyah Al Mujahidin Wonosari Telah Dibuka!</span>
            </div>
            <div class="flex items-center gap-4 text-[11px]">
                <span class="hidden md:inline">NPSN: 20402170 • Akreditasi A (Unggul) • Kab. Gunungkidul</span>
                <a href="#kontak" class="text-brand-700 hover:underline">Kontak</a>
            </div>
        </div>
    </div>

    <!-- MAIN NAVBAR -->
    <header class="sticky top-0 z-40 bg-surface/95 backdrop-blur-md border-b border-line">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand Logo -->
                <Link :href="route('welcome')" class="flex items-center gap-3 group">
                    <div class="w-11 h-11 rounded-control bg-brand-600 text-brand-fg flex items-center justify-center font-bold shadow-card">
                        <svg class="w-6 h-6 text-accent-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l1.5 3.5L17 4l-1 3.5 3.5 1-2.5 2.5 3 2.5-3.5 1 1 3.5-3.5-1-1.5 3.5-1.5-3.5L5 20l1-3.5-3.5-1 2.5-2.5-3-2.5 3.5-1-1-3.5 3.5 1L12 2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-lg sm:text-xl font-bold tracking-tight text-ink font-display">SD AL MUJAHIDIN</span>
                            <span class="hidden sm:inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 border border-brand-200">
                                WONOSARI
                            </span>
                        </div>
                        <span class="text-[11px] text-ink-muted block font-medium">
                            "Belia Mendunia" • Membangun Kecerdasan & Keshalehan
                        </span>
                    </div>
                </Link>

                <!-- Desktop Nav -->
                <nav class="hidden lg:flex items-center gap-6 text-xs font-semibold text-ink-muted">
                    <a href="#profil" class="hover:text-brand-700 transition-colors">Profil Sekolah</a>
                    <a href="#program" class="hover:text-brand-700 transition-colors">Program Unggulan</a>
                    <a href="#fasilitas" class="hover:text-brand-700 transition-colors">Fasilitas</a>
                    <a href="#berita" class="hover:text-brand-700 transition-colors">Berita & Prestasi</a>
                    <a href="#instagram" class="hover:text-brand-700 transition-colors">Instagram</a>
                    <a href="#kontak" class="hover:text-brand-700 transition-colors">Kontak</a>
                </nav>

                <!-- Action Buttons -->
                <div class="hidden sm:flex items-center gap-2.5">
                    <template v-if="$page.props.auth?.user">
                        <Link :href="route('dashboard')">
                            <AppButton variant="primary" size="sm">
                                Dashboard SIMSE
                            </AppButton>
                        </Link>
                    </template>
                    <template v-else>
                        <Link v-if="canLogin" :href="route('login')">
                            <AppButton variant="ghost" size="sm">
                                Portal SIMSE
                            </AppButton>
                        </Link>
                        <Link :href="route('ppdb.form')">
                            <AppButton variant="primary" size="sm">
                                Daftar Inden / PPDB →
                            </AppButton>
                        </Link>
                    </template>
                </div>

                <!-- Mobile Menu Button -->
                <div class="lg:hidden">
                    <AppButton
                        variant="ghost"
                        size="sm"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                    >
                        {{ mobileMenuOpen ? '✕ Tutup' : '☰ Menu' }}
                    </AppButton>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div v-show="mobileMenuOpen" class="lg:hidden border-b border-line bg-surface p-4 space-y-2 text-xs font-semibold">
            <a @click="mobileMenuOpen = false" href="#profil" class="block p-2 rounded-control text-ink hover:bg-surface-raised">Profil Sekolah</a>
            <a @click="mobileMenuOpen = false" href="#program" class="block p-2 rounded-control text-ink hover:bg-surface-raised">Program Unggulan</a>
            <a @click="mobileMenuOpen = false" href="#fasilitas" class="block p-2 rounded-control text-ink hover:bg-surface-raised">Fasilitas</a>
            <a @click="mobileMenuOpen = false" href="#berita" class="block p-2 rounded-control text-ink hover:bg-surface-raised">Berita & Prestasi</a>
            <a @click="mobileMenuOpen = false" href="#instagram" class="block p-2 rounded-control text-ink hover:bg-surface-raised">Instagram</a>
            <a @click="mobileMenuOpen = false" href="#kontak" class="block p-2 rounded-control text-ink hover:bg-surface-raised">Hubungi Kami</a>

            <div class="pt-3 border-t border-line flex flex-col gap-2">
                <template v-if="$page.props.auth?.user">
                    <Link :href="route('dashboard')">
                        <AppButton variant="primary" class="w-full">Dashboard SIMSE</AppButton>
                    </Link>
                </template>
                <template v-else>
                    <Link v-if="canLogin" :href="route('login')">
                        <AppButton variant="secondary" class="w-full">Portal SIMSE</AppButton>
                    </Link>
                    <Link :href="route('ppdb.form')">
                        <AppButton variant="primary" class="w-full">Daftar Inden / PPDB</AppButton>
                    </Link>
                </template>
            </div>
        </div>
    </header>
</template>

