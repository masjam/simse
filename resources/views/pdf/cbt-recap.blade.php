@extends('pdf.layout')

@section('content')
<div style="text-align: center; margin-bottom: 20px;">
    <h3 style="margin: 0; font-size: 13pt; text-transform: uppercase; font-weight: bold;">REKAPITULASI & ANALISIS UJIAN CBT</h3>
    <div style="font-size: 10pt; color: #555; margin-top: 4px;">Tahun Ajaran {{ $academic_year ?? '2026/2027' }}</div>
</div>

<table style="width: 100%; font-size: 9.5pt; margin-bottom: 15px; border-collapse: collapse;">
    <tr>
        <td style="width: 18%; font-weight: bold; padding: 3px 0;">Paket Ujian</td>
        <td style="width: 2%;">:</td>
        <td style="width: 45%;">{{ $exam->title }}</td>
        <td style="width: 18%; font-weight: bold; padding: 3px 0;">Guru Pengampu</td>
        <td style="width: 2%;">:</td>
        <td style="width: 15%;">{{ $exam->teacher?->user?->name ?? '-' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold; padding: 3px 0;">Mata Pelajaran</td>
        <td>:</td>
        <td>{{ $exam->subject?->name ?? '-' }}</td>
        <td style="font-weight: bold; padding: 3px 0;">Total Peserta</td>
        <td>:</td>
        <td>{{ $stats['total_participants'] }} Siswa</td>
    </tr>
    <tr>
        <td style="font-weight: bold; padding: 3px 0;">Waktu Pelaksanaan</td>
        <td>:</td>
        <td>{{ $exam->start_time?->format('d/m/Y H:i') }} - {{ $exam->end_time?->format('H:i') }}</td>
        <td style="font-weight: bold; padding: 3px 0;">Nilai Rata-rata</td>
        <td>:</td>
        <td><strong>{{ $stats['avg_score'] }}</strong> (Tertinggi: {{ $stats['max_score'] }}, Terendah: {{ $stats['min_score'] }})</td>
    </tr>
</table>

<h4 style="font-size: 10pt; margin: 15px 0 6px 0; border-bottom: 1px solid #1a1a1a; padding-bottom: 3px;">A. REKAP NILAI SISWA</h4>
<table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 8.5pt; margin-bottom: 15px;">
    <thead>
        <tr style="background-color: #f3f4f6;">
            <th style="border: 1px solid #999; padding: 5px; width: 5%; text-align: center;">No</th>
            <th style="border: 1px solid #999; padding: 5px; width: 15%; text-align: center;">NIS</th>
            <th style="border: 1px solid #999; padding: 5px; width: 40%; text-align: left;">Nama Siswa</th>
            <th style="border: 1px solid #999; padding: 5px; width: 15%; text-align: center;">Kelas</th>
            <th style="border: 1px solid #999; padding: 5px; width: 10%; text-align: center;">Pelanggaran</th>
            <th style="border: 1px solid #999; padding: 5px; width: 15%; text-align: center;">Nilai</th>
        </tr>
    </thead>
    <tbody>
        @forelse($attempts as $index => $att)
        <tr>
            <td style="border: 1px solid #ccc; padding: 4px; text-align: center;">{{ $index + 1 }}</td>
            <td style="border: 1px solid #ccc; padding: 4px; text-align: center;">{{ $att->student?->nis ?? '-' }}</td>
            <td style="border: 1px solid #ccc; padding: 4px;">{{ $att->student?->user?->name ?? 'Siswa' }}</td>
            <td style="border: 1px solid #ccc; padding: 4px; text-align: center;">{{ $att->student?->studentClass?->name ?? '-' }}</td>
            <td style="border: 1px solid #ccc; padding: 4px; text-align: center;">{{ $att->violations_count ?? 0 }}x</td>
            <td style="border: 1px solid #ccc; padding: 4px; text-align: center; font-weight: bold;">
                {{ $att->score !== null ? number_format($att->score, 1) : '-' }}
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" style="border: 1px solid #ccc; padding: 8px; text-align: center; color: #888;">Belum ada peserta yang mengikuti ujian ini.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<h4 style="font-size: 10pt; margin: 15px 0 6px 0; border-bottom: 1px solid #1a1a1a; padding-bottom: 3px;">B. ANALISIS BUTIR SOAL</h4>
<table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
    <thead>
        <tr style="background-color: #f3f4f6;">
            <th style="border: 1px solid #999; padding: 5px; width: 6%; text-align: center;">No</th>
            <th style="border: 1px solid #999; padding: 5px; width: 22%; text-align: center;">Tipe Soal</th>
            <th style="border: 1px solid #999; padding: 5px; width: 44%; text-align: left;">Ringkasan Soal</th>
            <th style="border: 1px solid #999; padding: 5px; width: 14%; text-align: center;">Tingkat Kesulitan</th>
            <th style="border: 1px solid #999; padding: 5px; width: 14%; text-align: center;">Kategori</th>
        </tr>
    </thead>
    <tbody>
        @foreach($item_analysis as $idx => $item)
        <tr>
            <td style="border: 1px solid #ccc; padding: 4px; text-align: center;">{{ $idx + 1 }}</td>
            <td style="border: 1px solid #ccc; padding: 4px; text-align: center;">{{ $item['type'] }}</td>
            <td style="border: 1px solid #ccc; padding: 4px;">{{ \Illuminate\Support\Str::limit($item['content'], 65) }}</td>
            <td style="border: 1px solid #ccc; padding: 4px; text-align: center;">{{ $item['difficulty_pct'] }}</td>
            <td style="border: 1px solid #ccc; padding: 4px; text-align: center; font-weight: bold;">{{ $item['category'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table style="width: 100%; margin-top: 35px; border-collapse: collapse; page-break-inside: avoid;">
    <tr>
        <td style="width: 60%;"></td>
        <td style="width: 40%; text-align: center; font-size: 9.5pt;">
            Wonosari, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
            Guru Mata Pelajaran,<br><br><br><br>
            <strong><u>{{ $exam->teacher?->user?->name ?? 'Guru Pengampu' }}</u></strong><br>
            NIP. {{ $exam->teacher?->nip ?? '-' }}
        </td>
    </tr>
</table>
@endsection

