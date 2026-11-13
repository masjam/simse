<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    canRegister: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <footer id="kontak" class="bg-surface text-ink-muted py-14 border-t border-line text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Brand & Address -->
                <div class="md:col-span-2 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-control bg-brand-600 text-brand-fg flex items-center justify-center font-bold">
                            ★
                        </div>
                        <div>
                            <span class="text-base font-bold text-ink block">SD MUHAMMADIYAH AL MUJAHIDIN</span>
                            <span class="text-[11px] text-brand-700">NPSN: 20402170 • Akreditasi A (Unggul)</span>
                        </div>
                    </div>
                    <p class="text-xs text-ink-muted leading-relaxed">
                        Jl. Mayang, Gadungsari, Wonosari, Kabupaten Gunungkidul, D.I. Yogyakarta 55812.
                    </p>
                    <div class="space-y-1 text-xs">
                        <p><span class="text-ink-muted">Telepon:</span> <strong class="text-ink font-numeric">(0274) 391147</strong></p>
                        <p><span class="text-ink-muted">Email:</span> <strong class="text-brand-700">admin@sdmujahidin-wns.sch.id</strong></p>
                        <p><span class="text-ink-muted">Website:</span> <a href="https://www.sdmujahidin-wns.sch.id" target="_blank" rel="noopener noreferrer" class="text-brand-700 hover:underline">www.sdmujahidin-wns.sch.id</a></p>
                    </div>
                </div>

                <!-- Nav Links -->
                <div class="space-y-2">
                    <h4 class="text-ink font-bold uppercase tracking-wider text-xs">Navigasi Utama</h4>
                    <ul class="space-y-1.5">
                        <li><a href="#profil" class="hover:text-brand-700">Sambutan Kepala Sekolah</a></li>
                        <li><a href="#program" class="hover:text-brand-700">Program Belia Mendunia</a></li>
                        <li><a href="#program" class="hover:text-brand-700">Tahfidzul Qur’an & Award</a></li>
                        <li><a href="#fasilitas" class="hover:text-brand-700">Fasilitas Kampus Gadungsari</a></li>
                        <li><a href="#berita" class="hover:text-brand-700">Warta & Prestasi Siswa</a></li>
                        <li><a href="#instagram" class="hover:text-brand-700">Instagram Resmi</a></li>
                    </ul>
                </div>

                <!-- Portal Links -->
                <div class="space-y-2">
                    <h4 class="text-ink font-bold uppercase tracking-wider text-xs">Layanan Terpadu</h4>
                    <ul class="space-y-1.5">
                        <li><Link :href="route('login', { role: 'guru' })" class="hover:text-brand-700">Portal Guru & Pendidik</Link></li>
                        <li><Link :href="route('login', { role: 'siswa' })" class="hover:text-brand-700">Portal Siswa & Wali Santri</Link></li>
                        <li><Link :href="route('login', { role: 'staf' })" class="hover:text-brand-700">Portal Staf & Bendahara</Link></li>
                        <li><Link :href="route('login', { role: 'admin' })" class="hover:text-brand-700">Portal Master Admin</Link></li>
                        <li><Link v-if="canRegister" :href="route('ppdb.form')" class="text-brand-700 font-bold hover:underline">Pendaftaran Siswa Baru (PPDB) →</Link></li>
                    </ul>
                </div>
            </div>

            <div class="pt-6 border-t border-line flex flex-col sm:flex-row items-center justify-between text-ink-muted text-[11px] gap-2">
                <p>© 2026 SD Muhammadiyah Al Mujahidin Wonosari. Membangun Kecerdasan & Keshalehan. Terintegrasi SIMSE.</p>
                <p>Majelis Dikdasmen PDM Gunungkidul</p>
            </div>
        </div>
    </footer>
</template>

