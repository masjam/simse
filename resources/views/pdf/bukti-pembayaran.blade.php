@extends('pdf.layout')

@section('content')
<div class="doc-title">
    <h2>KUITANSI RESMI PEMBAYARAN KASIR</h2>
    <p>Nomor Kuitansi: {{ $receiptNumber ?? 'KW/'.date('Ym').'/'.rand(1000, 9999) }}</p>
</div>

<!-- Informasi Bukti Pembayaran -->
<table style="width: 100%; border-collapse: collapse; font-size: 10pt; margin-bottom: 16px;">
    <tr>
        <td style="width: 25%; font-weight: bold; padding: 4px 0;">Telah Diterima Dari</td>
        <td style="width: 3%;">:</td>
        <td style="padding: 4px 0;"><strong>{{ $studentName }}</strong> (Kelas: {{ $className }})</td>
    </tr>
    <tr>
        <td style="font-weight: bold; padding: 4px 0;">NIS / NISN</td>
        <td>:</td>
        <td style="padding: 4px 0;">{{ $studentNis ?? '-' }} / {{ $studentNisn ?? '-' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold; padding: 4px 0;">Uang Sejumlah</td>
        <td>:</td>
        <td style="padding: 4px 0; font-weight: bold; color: #0f766e; font-size: 11pt;">
            Rp {{ number_format($amount, 0, ',', '.') }}
        </td>
    </tr>
    <tr>
        <td style="font-weight: bold; padding: 4px 0; vertical-align: top;">Terbilang</td>
        <td style="vertical-align: top;">:</td>
        <td style="padding: 4px 0; font-style: italic; background-color: #f3f4f6; padding: 6px 8px; border-left: 3px solid #0f766e;">
            "{{ $terbilang ?? 'Tiga ratus lima puluh ribu rupiah' }}"
        </td>
    </tr>
    <tr>
        <td style="font-weight: bold; padding: 4px 0;">Untuk Pembayaran</td>
        <td>:</td>
        <td style="padding: 4px 0;">{{ $billName ?? 'SPP / Iuran Komite Sekolah' }} (Bulan: {{ $month ?? date('F Y') }})</td>
    </tr>
    <tr>
        <td style="font-weight: bold; padding: 4px 0;">Metode Pembayaran</td>
        <td>:</td>
        <td style="padding: 4px 0;">{{ $paymentMethod ?? 'Tunai (Kasir Sekolah)' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold; padding: 4px 0;">Status Pembayaran</td>
        <td>:</td>
        <td style="padding: 4px 0; color: #047857; font-weight: bold;">LUNAS (BERHASIL)</td>
    </tr>
</table>

<table class="signatures-table" style="margin-top: 36px;">
    <tr>
        <td style="width: 50%;">
            Penyetor / Orang Tua Siswa,
            <div style="height: 60px;"></div>
            <strong style="text-decoration: underline;">( .................................................. )</strong>
        </td>
        <td style="width: 50%;">
            Wonosari, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
            <strong>Kasir / Bendahara Sekolah,</strong>
            <div style="height: 60px;"></div>
            <strong style="text-decoration: underline;">{{ $cashierName ?? 'Bendahara Sekolah' }}</strong><br>
            <span>NBM. 1198273</span>
        </td>
    </tr>
</table>
@endsection

