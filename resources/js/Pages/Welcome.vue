<script setup>
import { Head } from '@inertiajs/vue3';
import PortalShell from '@/Layouts/PortalShell.vue';
import WelcomeNavbar from './Welcome/WelcomeNavbar.vue';
import HeroSection from './Welcome/HeroSection.vue';
import AboutSection from './Welcome/AboutSection.vue';
import ProgramsSection from './Welcome/ProgramsSection.vue';
import FacilitiesSection from './Welcome/FacilitiesSection.vue';
import NewsSection from './Welcome/NewsSection.vue';
import InstagramSection from './Welcome/InstagramSection.vue';
import CtaBannerSection from './Welcome/CtaBannerSection.vue';
import WelcomeFooter from './Welcome/WelcomeFooter.vue';

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
        default: true,
    },
    laravelVersion: {
        type: String,
        default: '',
    },
    phpVersion: {
        type: String,
        default: '',
    },
    instagramPosts: {
        type: Array,
        default: () => [],
    },
    latestNews: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="SD Muhammadiyah Al Mujahidin Wonosari - Belia Mendunia, Cerdas & Berakhlak" />

    <PortalShell variant="bare">
        <div class="min-h-screen bg-surface text-ink font-body selection:bg-brand-500 selection:text-white">
            <WelcomeNavbar :can-login="canLogin" />
            <main>
                <HeroSection :can-register="canRegister" />
                <AboutSection />
                <ProgramsSection />
                <FacilitiesSection />
                <NewsSection :news="latestNews" />
                <InstagramSection :posts="instagramPosts" />
                <CtaBannerSection :can-login="canLogin" />
            </main>
            <WelcomeFooter :can-register="canRegister" />
        </div>
    </PortalShell>
</template>
