import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Allowlist for legacy pages - all pages are now 100% migrated to Design System!
const SPRINT2_MIGRATION_ALLOWLIST = new Set([]);

const PAGES_DIR = path.resolve(__dirname, '../resources/js/Pages');

function getAllFiles(dir, fileList = []) {
    if (!fs.existsSync(dir)) return fileList;
    const files = fs.readdirSync(dir);
    for (const file of files) {
        const filePath = path.join(dir, file);
        if (fs.statSync(filePath).isDirectory()) {
            getAllFiles(filePath, fileList);
        } else if (file.endsWith('.vue')) {
            fileList.push(filePath);
        }
    }
    return fileList;
}

const rawColorRegex = /\b(bg|text|border|ring|fill|stroke)-(slate|gray|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose)-\d+\b/g;
const hexRgbHslRegex = /(#[0-9a-fA-F]{3,8}|rgba?\(|hsla?\()/g;
const styleAttrRegex = /\bstyle\s*=\s*["'][^"']+["']/g;
const rawElementsRegex = /<(button|input|select|textarea|table)[\s>]/g;
const windowAlertRegex = /\b(window\.)?alert\s*\(/g;
const windowConfirmRegex = /\bwindow\.confirm\s*\(/g;
const legacyLayoutsRegex = /from\s+['"]@\/Layouts\/(?!PortalShell)[A-Za-z0-9_]+(\.vue)?['"]/g;

console.log('--- Checking SIMSE UI Consistency & Design System Rules ---');

const allPages = getAllFiles(PAGES_DIR);
let errorsCount = 0;
let checkedCount = 0;
let skippedCount = 0;

for (const fullPath of allPages) {
    const relPath = path.relative(PAGES_DIR, fullPath).replace(/\\/g, '/');

    if (SPRINT2_MIGRATION_ALLOWLIST.has(relPath)) {
        skippedCount++;
        continue;
    }

    checkedCount++;
    const content = fs.readFileSync(fullPath, 'utf8');
    const issues = [];

    // (a) Raw colors
    const rawColors = content.match(rawColorRegex);
    if (rawColors) {
        issues.push(`Raw Tailwind color classes found: ${[...new Set(rawColors)].slice(0, 5).join(', ')}`);
    }

    // (b) Hex / rgb / hsl
    // Ignore inside <style> tags or svg paths if scoped
    const templateContent = content.replace(/<style[\s\S]*?<\/style>/gi, '');
    const hexColors = templateContent.match(hexRgbHslRegex);
    if (hexColors) {
        issues.push(`Hex/RGB/HSL color values found in template/script: ${[...new Set(hexColors)].slice(0, 5).join(', ')}`);
    }

    // (c) style=""
    const styleAttrs = templateContent.match(styleAttrRegex);
    if (styleAttrs) {
        issues.push(`Inline style attribute found: ${styleAttrs.slice(0, 3).join(', ')}`);
    }

    // (d) Raw elements outside components
    const rawElements = templateContent.match(rawElementsRegex);
    if (rawElements) {
        issues.push(`Raw HTML form/table elements found: ${[...new Set(rawElements)].join(', ')}. Use AppButton, AppInput, DataTable, etc.`);
    }

    // (e) alert() / confirm()
    if (windowAlertRegex.test(content) || windowConfirmRegex.test(content)) {
        issues.push('Native alert() or confirm() found. Use useToast() or useConfirm().');
    }

    // (f) Non-PortalShell layout import
    const invalidLayouts = content.match(legacyLayoutsRegex);
    if (invalidLayouts) {
        issues.push(`Legacy layout imported: ${invalidLayouts.join(', ')}. Must use PortalShell.vue.`);
    }

    if (issues.length > 0) {
        console.error(`\nFAIL: [${relPath}]`);
        for (const issue of issues) {
            console.error(`  - ${issue}`);
        }
        errorsCount += issues.length;
    } else {
        console.log(`PASS: [${relPath}]`);
    }
}

console.log(`\nSummary: ${checkedCount} new/migrated page(s) checked, ${skippedCount} legacy page(s) on Sprint 2 allowlist.`);

if (errorsCount > 0) {
    console.error(`\nUI Consistency check failed with ${errorsCount} violation(s)!`);
    process.exit(1);
} else {
    console.log('UI Consistency check passed cleanly! 100% compliant with SIMSE Design System.');
    process.exit(0);
}

