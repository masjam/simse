@extends('pdf.layout')

@section('content')
<div class="doc-title">
    <h2>BERITA ACARA PEMBINAAN KEDISIPLINAN SISWA (BAP)</h2>
    <p>Nomor: {{ $docNumber ?? 'BAP/BK/'.date('Ym').'/'.rand(100, 999) }}</p>
</div>

<p style="text-align: justify; font-size: 10pt; line-height: 1.5;">
    Pada hari ini, <strong>{{ \Carbon\Carbon::now()->translatedFormat('l') }}</strong> tanggal <strong>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</strong> bertempat di Ruang Bimbingan Konseling SD Muhammadiyah Al Mujahidin Wonosari, telah dilaksanakan proses pembinaan kedisiplinan dan konseling terhadap:
</p>

<table style="width: 100%; border-collapse: collapse; font-size: 9.5pt; margin: 8px 0 14px 12px;">
    <tr>
        <td style="width: 25%; font-weight: bold; padding: 3px 0;">Nama Lengkap Siswa</td>
        <td style="width: 3%;">:</td>
        <td style="padding: 3px 0;"><strong>{{ $studentName }}</strong></td>
    </tr>
    <tr>
        <td style="font-weight: bold; padding: 3px 0;">Kelas</td>
        <td>:</td>
        <td style="padding: 3px 0;">{{ $className }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold; padding: 3px 0;">Nama Orang Tua / Wali</td>
        <td>:</td>
        <td style="padding: 3px 0;">{{ $parentName ?? 'Orang Tua / Wali' }}</td>
    </tr>
</table>

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 30%;">Kronologi / Masalah</th>
            <th style="width: 40%;">Hasil Klarifikasi & Konseling</th>
            <th style="width: 30%;">Komitmen / Tindak Lanjut</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="vertical-align: top;">{{ $chronology ?? 'Terjadinya pelanggaran tata tertib sekolah terkait kedisiplinan belajar di lingkungan sekolah.' }}</td>
            <td style="vertical-align: top;">{{ $clarification ?? 'Siswa menyadari kekeliruan yang telah dilakukan dan berjanji untuk tidak mengulangi perbuatannya.' }}</td>
            <td style="vertical-align: top;">{{ $commitment ?? 'Siswa bersedia mematuhi tata tertib, dibimbing oleh wali kelas dan dipantau bersama oleh orang tua di rumah.' }}</td>
        </tr>
    </tbody>
</table>

<p style="text-align: justify; font-size: 9.5pt; line-height: 1.4; margin-top: 14px;">
    Demikian Berita Acara ini dibuat dengan sebenarnya dalam suasana kekeluargaan untuk dipergunakan sebagaimana mestinya demi pembinaan karakter peserta didik yang berkemajuan.
</p>

<table class="signatures-table" style="margin-top: 24px;">
    <tr>
        <td style="width: 33%;">
            Siswa Yang Bersangkutan,
            <div style="height: 55px;"></div>
            <strong style="text-decoration: underline;">{{ $studentName }}</strong>
        </td>
        <td style="width: 33%;">
            Orang Tua / Wali Murid,
            <div style="height: 55px;"></div>
            <strong style="text-decoration: underline;">( ......................................... )</strong>
        </td>
        <td style="width: 34%;">
            Guru Pembina / BK,
            <div style="height: 55px;"></div>
            <strong style="text-decoration: underline;">{{ $counselorName ?? 'Guru Pembina BK' }}</strong>
        </td>
    </tr>
</table>
@endsection

