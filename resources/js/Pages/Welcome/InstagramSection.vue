<script setup>
import AppCard from '@/Components/ui/AppCard.vue';
import AppButton from '@/Components/ui/AppButton.vue';

defineProps({
    posts: {
        type: Array,
        default: () => [],
    },
});

const defaultPosts = [
    {
        id: 1,
        img: 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=600&q=80',
        caption: 'Bismillah, melangkah dengan percaya diri membawa nama harum Gunungkidul! Momen delegasi siswa SD Muhammadiyah Al Mujahidin Wonosari bertolak ke Singapura & Malaysia ✈️ #BeliaMendunia #SDAMWonosari',
        permalink: 'https://www.instagram.com/sdmujahidinwnsofficial/?hl=en',
        likes: '942',
        comments: '78',
        time: '1 HARI LALU',
    },
    {
        id: 2,
        img: 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=600&q=80',
        caption: 'Disiplin, tangkas, dan berwibawa! Penampilan memukau Polisi Cilik (Polcil) SDAM yang selalu menghadirkan kebanggaan di setiap formasi barisan 👮‍♂️✨ #PolcilSDAM #KarakterDisiplin',
        permalink: 'https://www.instagram.com/sdmujahidinwnsofficial/?hl=en',
        likes: '815',
        comments: '53',
        time: '3 HARI LALU',
    },
    {
        id: 3,
        img: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=600&q=80',
        caption: 'Khidmat dan penuh haru... Munaqasyah dan Wisuda Akbar Tahfidzul Qur’an ananda tercinta. Semoga mahkota kemuliaan senantiasa tersemat untuk kedua orang tua 📖 #TahfidzSDAM',
        permalink: 'https://www.instagram.com/sdmujahidinwnsofficial/?hl=en',
        likes: '1.240',
        comments: '112',
        time: '5 HARI LALU',
    },
    {
        id: 4,
        img: 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=600&q=80',
        caption: 'Ceria, mandiri, dan tangguh di alam terbuka! Riang gembira kegiatan Kepanduan Hizbul Wathan (HW) Cilik dan pendekar Tapak Suci SDAM di Gadungsari 🏕️🥋 #HizbulWathan #TapakSuci',
        permalink: 'https://www.instagram.com/sdmujahidinwnsofficial/?hl=en',
        likes: '730',
        comments: '46',
        time: '1 MINGGU LALU',
    },
];
</script>

<template>
    <section id="instagram" class="py-16 sm:py-24 bg-surface border-b border-line">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <!-- Header Bar -->
            <AppCard class="flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-card">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-brand-50 text-brand-700 border border-brand-200 flex items-center justify-center font-bold text-xl shrink-0">
                        📷
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-ink font-mono">@sdmujahidinwnsofficial</h3>
                            <span class="text-xs text-brand-700 font-bold">✓ Terverifikasi</span>
                        </div>
                        <p class="text-xs text-ink-muted mt-0.5">
                            Akun Resmi SD Muhammadiyah Al Mujahidin Wonosari, Gunungkidul • <strong>Belia Mendunia</strong>
                        </p>
                    </div>
                </div>

                <div>
                    <a
                        href="https://www.instagram.com/sdmujahidinwnsofficial/?hl=en"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-block"
                    >
                        <AppButton variant="primary" size="sm">
                            Buka Instagram Resmi ↗
                        </AppButton>
                    </a>
                </div>
            </AppCard>

            <!-- Instagram Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a
                    v-for="post in (posts.length > 0 ? posts : defaultPosts)"
                    :key="post.id"
                    :href="post.permalink || 'https://www.instagram.com/sdmujahidinwnsofficial/?hl=en'"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="group relative rounded-card overflow-hidden aspect-square bg-surface-sunken border border-line shadow-card cursor-pointer block"
                >
                    <img
                        :src="post.img"
                        :alt="post.caption"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    />

                    <!-- Overlay on hover -->
                    <div class="absolute inset-0 bg-surface/90 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity p-4 flex flex-col justify-between text-ink">
                        <div class="flex items-center justify-between text-xs font-numeric text-ink-muted">
                            <span>❤️ {{ post.likes }}</span>
                            <span>💬 {{ post.comments }}</span>
                        </div>
                        <p class="text-[11px] text-ink line-clamp-4 leading-relaxed">
                            {{ post.caption }}
                        </p>
                        <span class="text-[10px] font-numeric text-ink-muted">
                            {{ post.time }}
                        </span>
                    </div>
                </a>
            </div>
        </div>
    </section>
</template>

