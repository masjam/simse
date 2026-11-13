<script setup>
import AppCard from '@/Components/ui/AppCard.vue';
</script>

<template>
    <section id="profil" class="py-16 sm:py-24 bg-surface border-b border-line">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="text-center max-w-3xl mx-auto space-y-3">
                <span class="text-xs font-bold tracking-wider text-brand-700 uppercase bg-brand-50 px-3 py-1 rounded-full border border-brand-200">
                    Profil Lembaga
                </span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-ink">
                    Membangun Kecerdasan & Keshalehan
                </h2>
                <p class="text-xs sm:text-sm text-ink-muted leading-relaxed">
                    Berdiri di bawah naungan Pimpinan Daerah Muhammadiyah Gunungkidul, SD Muhammadiyah Al Mujahidin hadir menjadi pionir sekolah dasar Islam terpadu yang memadukan adab, sains, dan wawasan mendunia.
                </p>
            </div>

            <!-- Sambutan Kepala Sekolah -->
            <AppCard class="p-6 sm:p-10 shadow-card">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    <div class="lg:col-span-4 text-center">
                        <div class="relative inline-block">
                            <div class="w-40 h-40 sm:w-48 sm:h-48 rounded-card overflow-hidden mx-auto border-2 border-brand-500 shadow-card">
                                <img
                                    src="https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=600&q=80"
                                    alt="Kepala Sekolah SD Muhammadiyah Al Mujahidin"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-base font-bold text-ink">Joko Kiswanto, S.Pd.</h3>
                            <p class="text-xs text-brand-700 font-medium">Kepala SD Muhammadiyah Al Mujahidin</p>
                        </div>
                    </div>

                    <div class="lg:col-span-8 space-y-4">
                        <div class="text-xs font-semibold text-brand-700 font-mono">
                            Assalamu’alaikum Warahmatullahi Wabarakatuh
                        </div>
                        <blockquote class="text-xs sm:text-sm text-ink leading-relaxed italic">
                            “Kami meyakini bahwa setiap anak lahir dengan fitrah kebaikan dan keunikan potensi kecerdasan. Melalui semboyan <strong>'Belia Mendunia'</strong>, SD Muhammadiyah Al Mujahidin Wonosari berkomitmen tidak hanya mendidik ananda pandai membaca dan menghafal Al-Qur'an, tetapi juga memiliki mentalitas tangguh, budi pekerti yang santun, serta kepercayaan diri untuk berprestasi di panggung global. Dengan implementasi sistem <strong>SIMSE</strong>, sinergi antara ustadz/ustadzah dan ayah-bunda terjalin semakin transparan, cepat, dan penuh keberkahan.”
                        </blockquote>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-4 border-t border-line text-xs">
                            <div class="p-3 rounded-control bg-surface-raised border border-line">
                                <p class="font-bold text-ink">Sekolah Ramah Bakat</p>
                                <p class="text-ink-muted mt-0.5">Pembelajaran tanpa tekanan gawai.</p>
                            </div>
                            <div class="p-3 rounded-control bg-surface-raised border border-line">
                                <p class="font-bold text-ink">Akreditasi A Unggul</p>
                                <p class="text-ink-muted mt-0.5">NPSN: 20402170 rujukan nasional.</p>
                            </div>
                            <div class="p-3 rounded-control bg-surface-raised border border-line">
                                <p class="font-bold text-ink">Belia Mendunia</p>
                                <p class="text-ink-muted mt-0.5">Pertukaran pelajar internasional.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </AppCard>

            <!-- Visi & Misi -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <AppCard class="space-y-4 shadow-card">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-control bg-brand-50 text-brand-700 flex items-center justify-center font-bold">
                            👁
                        </div>
                        <h3 class="text-lg font-bold text-ink">Visi Sekolah</h3>
                    </div>
                    <p class="text-xs sm:text-sm text-ink-muted leading-relaxed italic">
                        "Menjadi Sekolah Dasar Islam Terpadu yang unggul dalam bidang akademik bertumpu pada pribadi yang bertaqwa dan berakhlaqul karimah."
                    </p>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="px-2.5 py-1 rounded-control bg-surface-raised text-brand-700 border border-line">Ketaqwaan</span>
                        <span class="px-2.5 py-1 rounded-control bg-surface-raised text-brand-700 border border-line">Akhlaqul Karimah</span>
                        <span class="px-2.5 py-1 rounded-control bg-surface-raised text-brand-700 border border-line">Akademik Unggul</span>
                    </div>
                </AppCard>

                <AppCard class="space-y-4 shadow-card">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-control bg-accent-50 text-accent-700 flex items-center justify-center font-bold">
                            🎯
                        </div>
                        <h3 class="text-lg font-bold text-ink">Misi Utama</h3>
                    </div>
                    <ul class="space-y-2 text-xs text-ink-muted">
                        <li class="flex items-start gap-2">
                            <span class="w-4 h-4 rounded-full bg-brand-50 text-brand-700 flex items-center justify-center font-bold shrink-0 mt-0.5">1</span>
                            <span>Meningkatkan wawasan Al-Islam membentuk pribadi beriman, bertaqwa, dan berakhlak mulia.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-4 h-4 rounded-full bg-brand-50 text-brand-700 flex items-center justify-center font-bold shrink-0 mt-0.5">2</span>
                            <span>Menjadikan nilai Al-Qur'an dan As-Sunnah sebagai rujukan utama proses pendidikan.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-4 h-4 rounded-full bg-brand-50 text-brand-700 flex items-center justify-center font-bold shrink-0 mt-0.5">3</span>
                            <span>Meningkatkan kemampuan pemahaman dan hafalan Al-Qur'an (Tahfiz Juz 30 & 29).</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-4 h-4 rounded-full bg-brand-50 text-brand-700 flex items-center justify-center font-bold shrink-0 mt-0.5">4</span>
                            <span>Meningkatkan wawasan keilmuan, bahasa asing, dan pemanfaatan sistem cerdas SIMSE.</span>
                        </li>
                    </ul>
                </AppCard>
            </div>
        </div>
    </section>
</template>

