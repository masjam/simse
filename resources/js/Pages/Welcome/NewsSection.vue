<script setup>
import AppCard from '@/Components/ui/AppCard.vue';
import StatusBadge from '@/Components/ui/StatusBadge.vue';

defineProps({
    news: {
        type: Array,
        default: () => [],
    },
});

const defaultNews = [
    {
        id: 1,
        title: 'Delegasi Belia Mendunia SD Muhammadiyah Al Mujahidin Resmi Dilepas Menuju Singapura & Malaysia',
        category: 'Internasional',
        date: '28 Oktober 2026',
        readTime: '3 Menit',
        excerpt: 'Sebanyak 24 siswa-siswi berprestasi siap mengikuti student exchange di sekolah mitra internasional guna mengasah kemandirian dan wawasan global.',
        img: 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=800&q=80',
    },
    {
        id: 2,
        title: 'Wisuda Akbar Tahfidzul Qur’an & Penganugerahan SD Muhammadiyah Al Mujahidin Award 2026',
        category: 'Prestasi & Keagamaan',
        date: '22 Oktober 2026',
        readTime: '4 Menit',
        excerpt: 'Ratusan siswa-siswi sukses menuntaskan hafalan Juz 30 dan 29 dengan predikat Mumtaz. Disertai pemberian apresiasi istimewa bagi para teladan akhlak dan sains.',
        img: 'https://images.unsplash.com/photo-1609599006353-e629aaabfeae?auto=format&fit=crop&w=800&q=80',
    },
    {
        id: 3,
        title: 'Polisi Cilik (Polcil) SDAM Tampil Memukau pada Peringatan Hari Lalu Lintas Bhayangkara',
        category: 'Karakter & Disiplin',
        date: '16 Oktober 2026',
        readTime: '3 Menit',
        excerpt: 'Kekompakan baris-berbaris dan ketegasan formasi ananda Polcil SDAM menuai decak kagum jajaran Forkopimda dan kepolisian di alun-alun Wonosari.',
        img: 'https://images.unsplash.com/photo-1517649763962-0c623266ddc0?auto=format&fit=crop&w=800&q=80',
    },
];
</script>

<template>
    <section id="berita" class="py-16 sm:py-24 bg-surface-sunken border-b border-line">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <span class="text-xs font-bold tracking-wider text-brand-700 uppercase bg-brand-50 px-3 py-1 rounded-full border border-brand-200">
                        Warta & Prestasi
                    </span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-ink mt-3">
                        Kabar Terkini Al Mujahidin
                    </h2>
                    <p class="mt-2 text-xs sm:text-sm text-ink-muted max-w-2xl leading-relaxed">
                        Ragam prestasi membanggakan, agenda kemitraan internasional, serta aktivitas kesiswaan SD Muhammadiyah Al Mujahidin Wonosari.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <AppCard
                    v-for="item in (news.length > 0 ? news : defaultNews)"
                    :key="item.id"
                    padding="none"
                    class="overflow-hidden shadow-card flex flex-col justify-between hover:border-brand-500 transition-all"
                >
                    <div>
                        <div class="relative aspect-[16/10] overflow-hidden bg-surface-sunken">
                            <img
                                :src="item.img"
                                :alt="item.title"
                                class="w-full h-full object-cover"
                            />
                            <div class="absolute top-3 left-3">
                                <StatusBadge
                                    variant="brand"
                                    :text="item.category"
                                    size="sm"
                                />
                            </div>
                        </div>

                        <div class="p-5 space-y-2">
                            <div class="flex items-center gap-2 text-[11px] text-ink-muted">
                                <span>{{ item.date }}</span>
                                <span>•</span>
                                <span>{{ item.readTime }}</span>
                            </div>
                            <h3 class="text-base font-bold text-ink line-clamp-2">
                                {{ item.title }}
                            </h3>
                            <p class="text-xs text-ink-muted leading-relaxed line-clamp-3">
                                {{ item.excerpt }}
                            </p>
                        </div>
                    </div>

                    <div class="p-5 pt-0">
                        <span class="text-xs font-bold text-brand-700 hover:underline cursor-pointer inline-flex items-center gap-1">
                            Baca Selengkapnya →
                        </span>
                    </div>
                </AppCard>
            </div>
        </div>
    </section>
</template>

