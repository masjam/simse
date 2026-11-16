@extends('pdf.layout')

@section('content')
<div class="doc-title">
    <h2>LAPORAN HASIL BELAJAR PESERTA DIDIK (e-RAPORT)</h2>
    <p>Tahun Pelajaran: {{ $academicYear ?? '2026/2027' }} | Semester: {{ $semester ?? 'Ganjil' }}</p>
</div>

<!-- Identitas Siswa -->
<table style="width: 100%; border-collapse: collapse; font-size: 9.5pt; margin-bottom: 12px;">
    <tr>
        <td style="width: 18%; font-weight: bold; padding: 3px 0;">Nama Peserta Didik</td>
        <td style="width: 2%;">:</td>
        <td style="width: 35%; padding: 3px 0;">{{ $student->user->name ?? $studentName }}</td>
        <td style="width: 18%; font-weight: bold; padding: 3px 0;">Kelas / Rombel</td>
        <td style="width: 2%;">:</td>
        <td style="width: 25%; padding: 3px 0;">{{ $student->studentClass->name ?? $className }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold; padding: 3px 0;">NIS / NISN</td>
        <td>:</td>
        <td style="padding: 3px 0;">{{ $student->nis ?? '-' }} / {{ $student->nisn ?? '-' }}</td>
        <td style="font-weight: bold; padding: 3px 0;">Fase / Kurikulum</td>
        <td>:</td>
        <td style="padding: 3px 0;">Fase {{ $fase ?? 'B' }} (Kurikulum Merdeka)</td>
    </tr>
</table>

<!-- Tabel Capaian Nilai Mapel -->
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 30%;">Mata Pelajaran</th>
            <th style="width: 10%;">KKM</th>
            <th style="width: 12%;">Nilai Akhir</th>
            <th style="width: 10%;">Predikat</th>
            <th style="width: 33%;">Capaian Kompetensi / Catatan Guru</th>
        </tr>
    </thead>
    <tbody>
        @forelse($grades as $index => $g)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td><strong>{{ $g['subject'] }}</strong></td>
            <td class="text-center">{{ $g['kkm'] ?? 75 }}</td>
            <td class="text-center" style="font-weight: bold;">{{ number_format($g['score'], 1) }}</td>
            <td class="text-center"><strong>{{ $g['grade'] }}</strong></td>
            <td style="font-size: 8.5pt;">{{ $g['notes'] ?? 'Menunjukkan penguasaan yang sangat baik dalam memahami materi pembelajaran.' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center" style="padding: 16px;">Belum ada data nilai semester ini.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<!-- Rangkuman Kehadiran & Kepribadian -->
<table style="width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 9pt;">
    <tr>
        <td style="width: 48%; vertical-align: top;">
            <table class="data-table" style="margin: 0;">
                <thead>
                    <tr>
                        <th colspan="2">Ketidakhadiran</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Sakit</td><td class="text-center" style="width: 25%;">{{ $attendanceSummary['sakit'] ?? 0 }} hari</td></tr>
                    <tr><td>Izin</td><td class="text-center">{{ $attendanceSummary['izin'] ?? 0 }} hari</td></tr>
                    <tr><td>Tanpa Keterangan (Alpa)</td><td class="text-center">{{ $attendanceSummary['alpa'] ?? 0 }} hari</td></tr>
                </tbody>
            </table>
        </td>
        <td style="width: 4%;"></td>
        <td style="width: 48%; vertical-align: top;">
            <table class="data-table" style="margin: 0;">
                <thead>
                    <tr>
                        <th>Catatan Perkembangan Karakter Islami</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="height: 62px; vertical-align: top; font-style: italic; font-size: 8.5pt;">
                            "Ananda memiliki budi pekerti yang santun, aktif dalam ibadah sholat dhuha dan sholat berjamaah, serta tekun dalam muraja'ah tahfidz Al-Qur'an."
                        </td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>

<!-- 3 BLOK TANDA TANGAN RESMI: Orang Tua, Wali Kelas, Kepala Sekolah -->
<table class="signatures-table" style="margin-top: 30px;">
    <tr>
        <td style="width: 33%;">
            Mengetahui,<br>
            <strong>Orang Tua / Wali Siswa</strong>
            <div style="height: 60px;"></div>
            <strong style="text-decoration: underline;">( .................................................. )</strong>
        </td>
        <td style="width: 33%;">
            Wonosari, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
            <strong>Wali Kelas</strong>
            <div style="height: 60px;"></div>
            <strong style="text-decoration: underline;">{{ $teacherName ?? ($student->studentClass->teacher->name ?? 'Wali Kelas') }}</strong><br>
            <span>NBM / NIP. {{ $teacherNip ?? '-' }}</span>
        </td>
        <td style="width: 34%;">
            Mengetahui,<br>
            <strong>Kepala Sekolah</strong>
            <div style="height: 60px;"></div>
            <strong style="text-decoration: underline;">{{ $school['principal'] }}</strong><br>
            <span>{{ $school['principal_nip'] }}</span>
        </td>
    </tr>
</table>
@endsection

