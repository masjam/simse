import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

function hexToRgb(hex) {
    hex = hex.replace('#', '').trim();
    if (hex.length === 3) {
        hex = hex.split('').map(c => c + c).join('');
    }
    const num = parseInt(hex, 16);
    return {
        r: (num >> 16) & 255,
        g: (num >> 8) & 255,
        b: num & 255
    };
}

function getLuminance({ r, g, b }) {
    const a = [r, g, b].map(v => {
        v /= 255;
        return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
    });
    return a[0] * 0.2126 + a[1] * 0.7152 + a[2] * 0.0722;
}

function getContrastRatio(hex1, hex2) {
    const l1 = getLuminance(hexToRgb(hex1));
    const l2 = getLuminance(hexToRgb(hex2));
    const lighter = Math.max(l1, l2);
    const darker = Math.min(l1, l2);
    return (lighter + 0.05) / (darker + 0.05);
}

function parseTokens(css) {
    const themeBlocks = {};
    // Match block: selector { body }
    const blockRegex = /([^{]+)\{([^}]+)\}/g;
    let match;
    while ((match = blockRegex.exec(css)) !== null) {
        const selector = match[1];
        const body = match[2];

        const vars = {};
        for (const line of body.split(';')) {
            const clean = line.replace(/\/\*.*?\*\//g, '').trim();
            if (!clean.includes(':')) continue;
            const colonIdx = clean.indexOf(':');
            const key = clean.substring(0, colonIdx).trim();
            const val = clean.substring(colonIdx + 1).trim();
            if (key.startsWith('--') && val.startsWith('#')) {
                vars[key] = val.split(' ')[0]; // in case of extra rules
            }
        }

        const themes = [];
        if (selector.includes('madinah-gelap') || selector.includes('uji-gelap')) {
            themes.push('madinah-gelap', 'uji-gelap');
        } else if (selector.includes('madinah') || selector.includes(':root')) {
            themes.push('madinah');
        }

        for (const t of themes) {
            themeBlocks[t] = { ...(themeBlocks[t] || {}), ...vars };
        }
    }
    return themeBlocks;
}

const tokensFile = path.resolve(__dirname, '../resources/css/tokens.css');
if (!fs.existsSync(tokensFile)) {
    console.error(`Tokens file not found at ${tokensFile}`);
    process.exit(1);
}

const css = fs.readFileSync(tokensFile, 'utf8');
const themes = parseTokens(css);

const pairsToCheck = [
    { label: 'ink / surface (text)', fg: '--ink', bg: '--surface', min: 4.5 },
    { label: 'ink-muted / surface (muted text)', fg: '--ink-muted', bg: '--surface', min: 4.5 },
    { label: 'brand-fg / brand-600 (button text)', fg: '--brand-fg', bg: '--brand-600', min: 4.5 },
    { label: 'success-fg / success-bg (status)', fg: '--success-fg', bg: '--success-bg', min: 4.5 },
    { label: 'warning-fg / warning-bg (status)', fg: '--warning-fg', bg: '--warning-bg', min: 4.5 },
    { label: 'danger-fg / danger-bg (status)', fg: '--danger-fg', bg: '--danger-bg', min: 4.5 },
    { label: 'info-fg / info-bg (status)', fg: '--info-fg', bg: '--info-bg', min: 4.5 },
];

let failed = false;
console.log('--- Checking Design Token Contrast Ratios (WCAG AA) ---');

for (const [themeName, tokens] of Object.entries(themes)) {
    console.log(`\nTheme: [${themeName}]`);
    for (const check of pairsToCheck) {
        const fgHex = tokens[check.fg];
        const bgHex = tokens[check.bg];
        if (!fgHex || !bgHex) {
            console.error(`  FAIL: Missing token ${check.fg} or ${check.bg} in theme ${themeName}`);
            failed = true;
            continue;
        }

        const ratio = getContrastRatio(fgHex, bgHex);
        const pass = ratio >= check.min;
        const formattedRatio = ratio.toFixed(2) + ':1';

        if (pass) {
            console.log(`  PASS: ${check.label} -> ${formattedRatio} (min ${check.min}:1)`);
        } else {
            console.error(`  FAIL: ${check.label} -> ${formattedRatio} is below min ${check.min}:1 (${fgHex} on ${bgHex})`);
            failed = true;
        }
    }
}

if (failed) {
    console.error('\nContrast checks failed! Fix token values in resources/css/tokens.css');
    process.exit(1);
} else {
    console.log('\nAll contrast checks passed successfully!');
    process.exit(0);
}
