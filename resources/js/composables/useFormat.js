/**
 * SIMSE Formatting Composable
 * Localization: id-ID, Currency: IDR (Rp), Timezone: Asia/Jakarta
 */

export function useFormat() {
    function formatRupiah(amount, { showSymbol = true, fallback = 'Rp 0' } = {}) {
        if (amount === null || amount === undefined || isNaN(Number(amount))) {
            return fallback;
        }
        const num = Number(amount);
        const formatted = new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 0,
        }).format(num);

        return showSymbol ? `Rp ${formatted}` : formatted;
    }

    function formatDate(dateInput, style = 'panjang') {
        if (!dateInput) return '-';
        const date = new Date(dateInput);
        if (isNaN(date.getTime())) return '-';

        const optionsMap = {
            panjang: { day: 'numeric', month: 'long', year: 'numeric' },
            pendek: { day: 'numeric', month: 'short', year: 'numeric' },
            lengkap: {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            },
            waktu: { hour: '2-digit', minute: '2-digit' },
            bulanTahun: { month: 'long', year: 'numeric' },
        };

        const opts = optionsMap[style] || optionsMap.panjang;
        return new Intl.DateTimeFormat('id-ID', {
            ...opts,
            timeZone: 'Asia/Jakarta',
        }).format(date);
    }

    function formatNumber(num, decimals = 0) {
        if (num === null || num === undefined || isNaN(Number(num))) {
            return '0';
        }
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        }).format(Number(num));
    }

    function formatPercent(num, decimals = 1) {
        if (num === null || num === undefined || isNaN(Number(num))) {
            return '0%';
        }
        return `${formatNumber(num, decimals)}%`;
    }

    return {
        formatRupiah,
        formatDate,
        formatNumber,
        formatPercent,
    };
}

