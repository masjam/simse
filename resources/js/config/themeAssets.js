/**
 * Peta Aset & Ilustrasi Per Tema SIMSE
 * Digunakan oleh komponen EmptyState, Avatar Maskot, dan Header
 * tanpa percabangan logika di dalam halaman.
 */

export const themeAssets = {
    'madinah': {
        name: 'Madinah',
        badge: 'Islami & Berwibawa',
        emptyIllustration: 'mosque-stars',
        mascot: 'santri-teladan',
        accentIcon: 'crescent-star',
    },
    'taman-belajar': {
        name: 'Taman Belajar',
        badge: 'Ceria & Ramah Anak',
        emptyIllustration: 'open-story-book',
        mascot: 'sahabat-cilik',
        accentIcon: 'sun-sparkle',
    },
    'kertas-tinta': {
        name: 'Kertas dan Tinta',
        badge: 'Editorial & Pustaka',
        emptyIllustration: 'quill-notebook',
        mascot: 'pena-cendekia',
        accentIcon: 'stamp-seal',
    },
    'ledger-tegas': {
        name: 'Ledger Tegas',
        badge: 'Presisi & Akuntansi',
        emptyIllustration: 'calculator-sheet',
        mascot: 'neraca-adil',
        accentIcon: 'shield-check',
    },
    'senja-kaca': {
        name: 'Senja Kaca',
        badge: 'Teduh & Kehangatan Keluarga',
        emptyIllustration: 'family-home',
        mascot: 'bintang-keluarga',
        accentIcon: 'heart-hand',
    },
    'pasar-kantin': {
        name: 'Pasar Kantin',
        badge: 'Segar & Selera Makanan',
        emptyIllustration: 'canteen-tray',
        mascot: 'koki-ceria',
        accentIcon: 'apple-fork',
    },
    'fokus-ujian': {
        name: 'Fokus Ujian',
        badge: 'Bebas Gangguan',
        emptyIllustration: 'focus-target',
        mascot: 'mata-jeli',
        accentIcon: 'target-bullseye',
    },
    'madinah-gelap': {
        name: 'Madinah Gelap',
        badge: 'Mode Malam',
        emptyIllustration: 'moon-stars',
        mascot: 'santri-teladan',
        accentIcon: 'moon-night',
    },
    'uji-gelap': {
        name: 'Uji Gelap',
        badge: 'Kontras Tinggi',
        emptyIllustration: 'contrast-grid',
        mascot: 'neraca-adil',
        accentIcon: 'contrast-eye',
    },
};

export function getThemeAsset(theme, assetKey, fallback = '') {
    return themeAssets[theme]?.[assetKey] || themeAssets['madinah']?.[assetKey] || fallback;
}

