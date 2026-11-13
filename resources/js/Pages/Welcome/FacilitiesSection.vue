<script setup>
import AppCard from '@/Components/ui/AppCard.vue';

const facilities = [
    {
        name: 'Masjid Al Mujahidin Kampus Gadungsari',
        category: 'Ibadah & Karakter',
        desc: 'Pusat pembiasaan sholat dhuha, sholat dhuhur berjamaah, dan tadarus pagi. Bersih, sejuk ber-AC, berkarpet empuk, dan ramah untuk ananda berlatih ibadah tepat waktu.',
        img: 'https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?auto=format&fit=crop&w=800&q=80',
        spec: 'Kapasitas 600 Jamaah • Ber-AC',
    },
    {
        name: 'Laboratorium Komputer & CBT Center',
        category: 'Teknologi & Sains',
        desc: 'Dilengkapi puluhan unit PC all-in-one modern, jaringan intranet gigabit berkecepatan tinggi, serta sistem ujian terproteksi untuk pelaksanaan Asesmen Nasional (ANBK) dan CBT SIMSE.',
        img: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=800&q=80',
        spec: '40 Unit PC All-in-One • Fiber Optic',
    },
    {
        name: 'Perpustakaan Digital Ki Bagus Hadikusumo',
        category: 'Literasi & Riset',
        desc: 'Ruang baca ramah anak dengan ribuan koleksi buku fiksi, ensiklopedia sains anak, literatur keislaman, serta pojok baca digital terintegrasi e-katalog.',
        img: 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=800&q=80',
        spec: 'Ribuan Koleksi Buku • Corner Santai',
    },
    {
        name: 'e-Kantin Sehat Cashless RFID',
        category: 'Layanan Siswa',
        desc: 'Kantin sekolah higienis bebas MSG berbahaya, menyediakan makanan sehat bergizi yang diawasi berkala. Transaksi praktis non-tunai menggunakan kartu RFID siswa dengan limit harian teratur.',
        img: 'https://images.unsplash.com/photo-1567521464027-f127ff144326?auto=format&fit=crop&w=800&q=80',
        spec: 'Sertifikasi Higienis • Transaksi RFID',
    },
    {
        name: 'Ruang Kelas Representatif Ber-AC & Proyektor',
        category: 'Sarana Belajar',
        desc: 'Setiap ruang kelas dilengkapi AC, proyektor LCD/smart display interaktif, pencahayaan alami optimal, serta tata meja fleksibel yang mendukung active collaborative learning.',
        img: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=800&q=80',
        spec: '24 Ruang Kelas Ber-AC • Smart Display',
    },
    {
        name: 'Lapangan Olahraga & Arena Kreativitas Siswa',
        category: 'Olahraga & Seni',
        desc: 'Area multifungsi terpadu untuk latihan baris Polcil, apel pagi, upacara bendera, drumband Gita Mujahidin, futsal, bulutangkis, serta latihan seni bela diri Tapak Suci.',
        img: 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=800&q=80',
        spec: 'Lapangan Multifungsi Luas',
    },
];
</script>

<template>
    <section id="fasilitas" class="py-16 sm:py-24 bg-surface border-b border-line">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <span class="text-xs font-bold tracking-wider text-brand-700 uppercase bg-brand-50 px-3 py-1 rounded-full border border-brand-200">
                        Sarana & Fasilitas
                    </span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-ink mt-3">
                        Fasilitas Kampus Gadungsari
                    </h2>
                    <p class="mt-2 text-xs sm:text-sm text-ink-muted max-w-2xl leading-relaxed">
                        Didukung sarana prasarana modern, ruang belajar ber-AC, masjid luas, area terbuka hijau, dan ekosistem digital SIMSE yang nyaman untuk ananda.
                    </p>
                </div>
                <div class="text-left md:text-right">
                    <span class="text-xs text-ink-muted block font-medium">Standar Kampus:</span>
                    <span class="text-xs sm:text-sm font-bold text-accent-700 flex items-center gap-1.5 justify-start md:justify-end mt-1">
                        <span class="w-2 h-2 rounded-full bg-brand-600 animate-pulse"></span>
                        Full Day School Ramah Anak & Ber-AC
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <AppCard
                    v-for="(fac, index) in facilities"
                    :key="index"
                    padding="none"
                    class="overflow-hidden shadow-card flex flex-col justify-between hover:border-brand-500 transition-all"
                >
                    <div>
                        <div class="relative aspect-[16/10] overflow-hidden bg-surface-sunken">
                            <img
                                :src="fac.img"
                                :alt="fac.name"
                                class="w-full h-full object-cover"
                            />
                            <span class="absolute top-3 right-3 px-2.5 py-0.5 rounded-full bg-surface/90 backdrop-blur-sm text-[11px] font-bold text-brand-700 border border-line">
                                {{ fac.category }}
                            </span>
                        </div>

                        <div class="p-5 space-y-2">
                            <h3 class="text-base font-bold text-ink">
                                {{ fac.name }}
                            </h3>
                            <p class="text-xs text-ink-muted leading-relaxed">
                                {{ fac.desc }}
                            </p>
                        </div>
                    </div>

                    <div class="px-5 pb-5 pt-3 border-t border-line text-xs font-numeric text-brand-700 font-semibold">
                        {{ fac.spec }}
                    </div>
                </AppCard>
            </div>
        </div>
    </section>
</template>

