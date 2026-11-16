@extends('pdf.layout')

@section('content')
<div style="text-align: center; margin: 20px 0 10px 0;">
    <h1 style="font-size: 22pt; font-weight: 800; color: #0f766e; letter-spacing: 2px; margin: 0; text-transform: uppercase;">
        PIAGAM PENGHARGAAN
    </h1>
    <p style="font-size: 10pt; color: #4b5563; margin-top: 4px;">
        Nomor Sertifikat: {{ $certificateNumber ?? 'SERT/PRESTASI/MAM/'.date('Y').'/'.rand(100, 999) }}
    </p>
</div>

<p style="text-align: center; font-size: 11pt; margin-top: 18px;">
    Kepala Sekolah Dasar Muhammadiyah Al Mujahidin Wonosari memberikan penghargaan setinggi-tingginya kepada:
</p>

<div style="text-align: center; margin: 20px 0;">
    <h2 style="font-size: 20pt; font-weight: bold; color: #111827; text-decoration: underline; margin: 0;">
        {{ $studentName }}
    </h2>
    <p style="font-size: 11pt; color: #374151; margin-top: 6px;">
        NIS / NISN: {{ $studentNis ?? '-' }} / {{ $studentNisn ?? '-' }} &bull; Kelas: {{ $className }}
    </p>
</div>

<p style="text-align: center; font-size: 12pt; font-style: italic; margin: 0 40px; line-height: 1.6;">
    Atas capaian gemilang dan dedikasi yang tinggi sebagai:<br>
    <strong style="font-size: 14pt; color: #b45309; font-style: normal; display: inline-block; margin-top: 6px;">
        {{ $achievementTitle ?? 'Juara I Lomba Tahfidz Al-Qur'an Tingkat Kabupaten' }}
    </strong>
</p>

<p style="text-align: center; font-size: 10pt; color: #4b5563; margin-top: 16px;">
    Diselenggarakan pada kegiatan: {{ $eventDescription ?? 'Semarak Milad Muhammadiyah & Pentas Prestasi' }} (Tahun {{ $year ?? date('Y') }})
</p>

<table class="signatures-table" style="margin-top: 40px;">
    <tr>
        <td style="width: 50%;">
            <!-- Left QR verification placeholder if needed -->
            @if(isset($qrCode))
            <div style="text-align: center;">
                <img src="{{ $qrCode }}" alt="QR Keaslian" style="width: 70px; height: 70px;"><br>
                <span style="font-size: 7.5pt; color: #6b7280;">Scan untuk verifikasi keaslian piagam</span>
            </div>
            @endif
        </td>
        <td style="width: 50%;">
            Wonosari, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
            <strong>Kepala Sekolah,</strong>
            <div style="height: 60px;"></div>
            <strong style="text-decoration: underline; font-size: 11pt;">{{ $school['principal'] }}</strong><br>
            <span>{{ $school['principal_nip'] }}</span>
        </td>
    </tr>
</table>
@endsection

