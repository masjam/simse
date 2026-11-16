@extends('pdf.layout')

@section('content')
<div class="doc-title">
    <h2>SURAT PERINGATAN {{ $spLevel ?? 'SP-1' }}</h2>
    <p>Nomor: {{ $docNumber ?? 'SP/BK/MAM/'.date('Y').'/042' }}</p>
</div>

<p style="text-align: justify; font-size: 10pt; line-height: 1.5;">
    Surat peringatan ini diterbitkan oleh pihak Bimbingan dan Konseling SD Muhammadiyah Al Mujahidin Wonosari kepada peserta didik di bawah ini:
</p>

<table style="width: 100%; border-collapse: collapse; font-size: 10pt; margin: 12px 0 16px 12px;">
    <tr>
        <td style="width: 25%; font-weight: bold; padding: 4px 0;">Nama Siswa</td>
        <td style="width: 3%;">:</td>
        <td style="padding: 4px 0;"><strong>{{ $studentName }}</strong></td>
    </tr>
    <tr>
        <td style="font-weight: bold; padding: 4px 0;">NIS / NISN</td>
        <td>:</td>
        <td style="padding: 4px 0;">{{ $studentNis ?? '-' }} / {{ $studentNisn ?? '-' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold; padding: 4px 0;">Kelas / Tingkat</td>
        <td>:</td>
        <td style="padding: 4px 0;">{{ $className }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold; padding: 4px 0;">Total Poin Pelanggaran</td>
        <td>:</td>
        <td style="padding: 4px 0; color: #dc2626; font-weight: bold;">{{ $points ?? '25' }} Poin</td>
    </tr>
</table>

<p style="text-align: justify; font-size: 10pt; line-height: 1.5;">
    Berdasarkan catatan kedisiplinan dan tata tertib sekolah, peserta didik yang bersangkutan telah melakukan pelanggaran sebagai berikut:
</p>

<div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 10px 14px; margin: 12px 0; font-size: 9.5pt;">
    <strong>Uraian Pelanggaran:</strong><br>
    {{ $violationDescription ?? 'Akumulasi keterlambatan hadir dan ketidakhadiran tanpa keterangan yang melampaui batas toleransi tata tertib sekolah.' }}
</div>

<p style="text-align: justify; font-size: 10pt; line-height: 1.5;">
    Sehubungan dengan hal tersebut, kami menghimbau kepada orang tua/wali siswa untuk bersama-sama memberikan pembinaan dan pendampingan yang lebih intensif di rumah. Peserta didik diharapkan dapat memperbaiki perilaku serta mentaati seluruh tata tertib yang berlaku di SD Muhammadiyah Al Mujahidin Wonosari.
</p>

<table class="signatures-table" style="margin-top: 36px;">
    <tr>
        <td style="width: 50%;">
            Wonosari, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
            <strong>Guru Bimbingan Konseling (BK)</strong>
            <div style="height: 65px;"></div>
            <strong style="text-decoration: underline;">{{ $counselorName ?? 'Guru Pembina BK' }}</strong><br>
            <span>NBM / NIP. {{ $counselorNip ?? '-' }}</span>
        </td>
        <td style="width: 50%;">
            Mengetahui,<br>
            <strong>Kepala Sekolah</strong>
            <div style="height: 65px;"></div>
            <strong style="text-decoration: underline;">{{ $school['principal'] }}</strong><br>
            <span>{{ $school['principal_nip'] }}</span>
        </td>
    </tr>
</table>
@endsection

