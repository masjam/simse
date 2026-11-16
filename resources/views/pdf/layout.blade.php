<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title ?? 'Dokumen Resmi' }} - {{ $school['name'] ?? 'SD Muhammadiyah Al Mujahidin' }}</title>
    <style>
        @page {
            margin: 1.2cm 1.5cm 1.5cm 1.5cm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat 2 Logo */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .kop-logo-left {
            width: 75px;
            vertical-align: middle;
            text-align: center;
        }
        .kop-logo-right {
            width: 75px;
            vertical-align: middle;
            text-align: center;
        }
        .kop-text {
            text-align: center;
            vertical-align: middle;
            padding: 0 10px;
        }
        .kop-text h4 {
            font-size: 10pt;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }
        .kop-text h3 {
            font-size: 11pt;
            font-weight: bold;
            margin: 2px 0;
            text-transform: uppercase;
        }
        .kop-text h2 {
            font-size: 14pt;
            font-weight: 800;
            color: #0f766e; /* Teal Mujahidin */
            margin: 3px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-text p {
            font-size: 8pt;
            color: #4b5563;
            margin: 2px 0 0 0;
            line-height: 1.2;
        }

        .kop-divider-thick {
            border-top: 2.5px solid #111827;
            margin-top: 6px;
        }
        .kop-divider-thin {
            border-top: 1px solid #111827;
            margin-top: 1.5px;
            margin-bottom: 16px;
        }

        /* Utility classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .w-full { width: 100%; }
        
        .doc-title {
            text-align: center;
            margin-bottom: 18px;
        }
        .doc-title h2 {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
            text-transform: uppercase;
        }
        .doc-title p {
            font-size: 9.5pt;
            color: #374151;
            margin: 2px 0 0 0;
        }

        /* Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 9.5pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #4b5563;
            padding: 5px 8px;
        }
        table.data-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-align: center;
        }

        /* Signatures block */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
            font-size: 9.5pt;
        }
        .signatures-table td {
            vertical-align: top;
            text-align: center;
            padding: 4px;
        }

        /* Verification QR Footer */
        .verification-footer {
            margin-top: 24px;
            padding: 8px 12px;
            border: 1px dashed #9ca3af;
            background-color: #f9fafb;
            font-size: 8pt;
            color: #4b5563;
        }
        .verification-footer table {
            width: 100%;
            border-collapse: collapse;
        }
    </style>
</head>
<body>
    <!-- KOP SURAT 2 LOGO -->
    <table class="kop-table">
        <tr>
            <!-- Logo Kiri: Majelis Dikdasmen -->
            <td class="kop-logo-left">
                <svg width="68" height="68" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="46" fill="#0f766e" />
                    <!-- Sun rays / Matahari Muhammadiyah -->
                    <circle cx="50" cy="50" r="32" fill="#fbbf24" />
                    <circle cx="50" cy="50" r="24" fill="#0f766e" />
                    <text x="50" y="55" font-family="DejaVu Sans" font-size="12" font-weight="bold" fill="#ffffff" text-anchor="middle">DIKDASMEN</text>
                </svg>
            </td>

            <!-- Teks Kop Tengah -->
            <td class="kop-text">
                <h4>{{ $school['yayasan'] ?? 'PIMPINAN CABANG MUHAMMADIYAH WONOSARI' }}</h4>
                <h3>{{ $school['majelis'] ?? 'MAJELIS PENDIDIKAN DASAR DAN MENENGAH' }}</h3>
                <h2>{{ $school['name'] ?? 'SD MUHAMMADIYAH AL MUJAHIDIN' }}</h2>
                <p>
                    {{ $school['address'] ?? 'Jl. KH. Agus Salim No. 35, Ledoksari, Kepek, Wonosari, Gunungkidul 55813' }}<br>
                    Telp: {{ $school['phone'] ?? '(0274) 391234' }} | Pos-el: {{ $school['email'] ?? 'info@sdmujahidin-wns.sch.id' }} | NPSN: {{ $school['npsn'] ?? '20402111' }}
                </p>
            </td>

            <!-- Logo Kanan: Sekolah SD Muhammadiyah Al Mujahidin -->
            <td class="kop-logo-right">
                <svg width="68" height="68" viewBox="0 0 100 100">
                    <polygon points="50,4 96,27 96,73 50,96 4,73 4,27" fill="#0f766e" stroke="#fbbf24" stroke-width="3" />
                    <circle cx="50" cy="50" r="28" fill="#ffffff" />
                    <text x="50" y="47" font-family="DejaVu Sans" font-size="10" font-weight="bold" fill="#0f766e" text-anchor="middle">SD MAM</text>
                    <text x="50" y="60" font-family="DejaVu Sans" font-size="7" font-weight="bold" fill="#b45309" text-anchor="middle">WONOSARI</text>
                </svg>
            </td>
        </tr>
    </table>

    <!-- Garis Pemisah Ganda -->
    <div class="kop-divider-thick"></div>
    <div class="kop-divider-thin"></div>

    <!-- KONTEN DOKUMEN -->
    @yield('content')

    <!-- FOOTER VERIFIKASI KEASLIAN -->
    @if(isset($verification) && isset($qrCode))
    <div class="verification-footer">
        <table>
            <tr>
                <td style="width: 65px; vertical-align: middle;">
                    <img src="{{ $qrCode }}" alt="QR Verifikasi" style="width: 55px; height: 55px;">
                </td>
                <td style="vertical-align: middle; padding-left: 8px;">
                    <strong>DOKUMEN RESMI TERVERIFIKASI ELEKTRONIK</strong><br>
                    Dokumen ini sah diterbitkan oleh sistem resmi SD Muhammadiyah Al Mujahidin Wonosari.<br>
                    Pindai kode QR untuk memverifikasi keaslian dokumen secara daring, atau kunjungi:<br>
                    <span style="color: #0f766e; word-break: break-all;">{{ $verifyUrl }}</span>
                </td>
            </tr>
        </table>
    </div>
    @endif
</body>
</html>

