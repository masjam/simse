<script setup>
import AppCard from '@/Components/ui/AppCard.vue';

const programs = [
    {
        title: 'Belia Mendunia (International Student Exchange)',
        category: 'Wawasan Global',
        desc: 'Program pertukaran pelajar internasional (International Asian & Community Program) ke Singapura & Malaysia untuk membekali ananda dengan wawasan global, kepercayaan diri, dan kecakapan komunikasi internasional sejak usia dini.',
        features: [
            'Pertukaran Pelajar Asia & Komunitas Global',
            'English Immersion & Cultural Exchange',
            'Pelepasan Resmi Pemkab Gunungkidul',
            'Kemandirian & Global Mindset Sejak Belia',
        ],
        badge: 'Belia Mendunia',
    },
    {
        title: 'Tahfidzul Qur’an & Al Mujahidin Award',
        category: 'Spiritual & Al-Qur’an',
        desc: 'Pembinaan hafalan Al-Qur’an intensif dengan target tuntas Juz 30 dan 29 melalui metode Tartil dan Tilawati. Diakhiri dengan Munaqasyah terbuka serta prosesi Wisuda Akbar Tahfidz bertabur penghargaan siswa berprestasi.',
        features: [
            'Bimbingan Tahsin & Tajwid Tilawati',
            'Target Hafalan Juz 30 & 29 Terukur',
            'Munaqasyah Terbuka Bersama Wali Santri',
            'Penganugerahan Al Mujahidin Award',
        ],
        badge: 'Qur’ani Unggul',
    },
    {
        title: 'Multiple Intelligences & Sekolah Ramah Bakat',
        category: 'Akademik & Karakter',
        desc: 'Memadukan kurikulum nasional dan nilai Al-Islam secara integratif. Menghargai setiap kecerdasan majemuk anak dengan pendekatan pembatasan gawai (non-gadget focus) di sekolah agar anak fokus bersosialisasi dan bereksplorasi.',
        features: [
            'Pembelajaran Berbasis Proyek Ramah Anak',
            'Lingkungan Terarah Tanpa Gangguan Gawai',
            'Pengembangan Minat Sains, Robotika & TIK',
            'Klub Olimpiade Sains & Matematika Nasional',
        ],
        badge: 'Ramah Bakat',
    },
    {
        title: 'Kader Pemimpin: Polcil, HW & Tapak Suci',
        category: 'Kepemimpinan & Kepanduan',
        desc: 'Mencetak kader persyarikatan dan bangsa yang tangguh, berdisiplin tinggi, dan berbudi luhur melalui Polisi Cilik (Polcil SDAM), Kepanduan Hizbul Wathan (HW), Drumband Gita Mujahidin, serta Tapak Suci Putera Muhammadiyah.',
        features: [
            'Polisi Cilik (Polcil SDAM) Juara Daerah',
            'Kemah Ceria Kepanduan Hizbul Wathan (HW)',
            'Pendekar Cilik Tapak Suci Berprestasi',
            'Drumband Gita Mujahidin Kebanggaan',
        ],
        badge: 'Karakter Tangguh',
    },
];
</script>

<template>
    <section id="program" class="py-16 sm:py-24 bg-surface-sunken border-b border-line">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="text-center max-w-3xl mx-auto space-y-3">
                <span class="text-xs font-bold tracking-wider text-brand-700 uppercase bg-brand-50 px-3 py-1 rounded-full border border-brand-200">
                    Keunggulan Pendidikan
                </span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-ink">
                    Program Unggulan Al Mujahidin
                </h2>
                <p class="text-xs sm:text-sm text-ink-muted leading-relaxed">
                    Mempersiapkan ananda menjadi generasi berkarakter unggul, berjiwa Al-Qur'an, cerdas berpikir ilmiah, dan percaya diri melangkah ke kancah global.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <AppCard
                    v-for="(prog, idx) in programs"
                    :key="idx"
                    class="flex flex-col justify-between shadow-card hover:border-brand-500 transition-all"
                >
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-brand-700 uppercase tracking-wider">
                                {{ prog.category }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                                {{ prog.badge }}
                            </span>
                        </div>

                        <h3 class="text-lg font-bold text-ink">
                            {{ prog.title }}
                        </h3>

                        <p class="text-xs text-ink-muted leading-relaxed">
                            {{ prog.desc }}
                        </p>
                    </div>

                    <div class="pt-4 mt-6 border-t border-line">
                        <p class="text-xs font-bold text-ink mb-2">Pilar & Kegiatan Program:</p>
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-ink-muted">
                            <li v-for="(feat, fIdx) in prog.features" :key="fIdx" class="flex items-center gap-1.5">
                                <span class="text-brand-700 font-bold">✓</span>
                                <span>{{ feat }}</span>
                            </li>
                        </ul>
                    </div>
                </AppCard>
            </div>
        </div>
    </section>
</template>

