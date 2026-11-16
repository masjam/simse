@extends('pdf.layout')

@section('content')
<div class="doc-title">
    <h2>KARTU TANDA SISWA (KTS) RESMI</h2>
    <p>SD Muhammadiyah Al Mujahidin Wonosari &bull; Berlaku Selama Menjadi Siswa Aktif</p>
</div>

<!-- Kartu Container Frame -->
<div style="border: 2px solid #0f766e; border-radius: 8px; padding: 16px; margin: 20px auto; max-width: 600px; background-color: #fdfdfd;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <!-- Pas Foto Siswa / QR -->
            <td style="width: 120px; vertical-align: top; text-align: center;">
                <div style="width: 100px; height: 130px; border: 1px solid #9ca3af; background-color: #f3f4f6; line-height: 130px; text-align: center; margin: 0 auto; font-size: 8pt; color: #6b7280;">
                    PAS FOTO 3x4
                </div>
                <div style="margin-top: 10px;">
                    @if(isset($qrCode))
                    <img src="{{ $qrCode }}" alt="Barcode KTS" style="width: 80px; height: 80px;">
                    @endif
                </div>
            </td>

            <!-- Biodata Siswa -->
            <td style="vertical-align: top; padding-left: 20px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 10pt;">
                    <tr>
                        <td style="width: 35%; font-weight: bold; padding: 4px 0;">Nomor Induk Siswa</td>
                        <td style="width: 5%;">:</td>
                        <td style="padding: 4px 0; font-weight: bold; color: #0f766e;">{{ $student->nis ?? $studentNis ?? '202601001' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 4px 0;">NISN</td>
                        <td>:</td>
                        <td style="padding: 4px 0;">{{ $student->nisn ?? $studentNisn ?? '0148923841' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 4px 0;">Nama Lengkap</td>
                        <td>:</td>
                        <td style="padding: 4px 0; font-weight: bold; font-size: 11pt;">{{ $student->user->name ?? $studentName }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 4px 0;">Tempat, Tgl Lahir</td>
                        <td>:</td>
                        <td style="padding: 4px 0;">{{ $student->tempat_lahir ?? $student->birth_place ?? 'Gunungkidul' }}, {{ ($student->tanggal_lahir ?? $student->birth_date ?? null) ? \Carbon\Carbon::parse($student->tanggal_lahir ?? $student->birth_date)->translatedFormat('d F Y') : '12 Januari 2018' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 4px 0;">Jenis Kelamin</td>
                        <td>:</td>
                        <td style="padding: 4px 0;">{{ $student->gender === 'L' ? 'Laki-Laki' : 'Perempuan' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 4px 0;">Tingkat & Rombel</td>
                        <td>:</td>
                        <td style="padding: 4px 0;">Kelas {{ $student->studentClass->name ?? $className }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 4px 0; vertical-align: top;">Alamat Siswa</td>
                        <td style="vertical-align: top;">:</td>
                        <td style="padding: 4px 0; font-size: 9pt;">{{ $student->address ?? 'Wonosari, Gunungkidul, D.I. Yogyakarta' }}</td>
                    </tr>
                </table>

                <div style="margin-top: 16px; border-top: 1px dashed #d1d5db; pt-2 text-align: right; font-size: 8.5pt;">
                    Wonosari, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    <strong>Kepala Sekolah</strong><br>
                    <div style="height: 35px;"></div>
                    <strong>{{ $school['principal'] }}</strong><br>
                    <span>{{ $school['principal_nip'] }}</span>
                </div>
            </td>
        </tr>
    </table>
</div>

<p style="text-align: center; font-size: 8pt; color: #6b7280; margin-top: 20px;">
    * Kartu ini adalah tanda pengenal resmi peserta didik SD Muhammadiyah Al Mujahidin Wonosari. Jika menemukan kartu ini, harap menghubungi pihak sekolah.
</p>
@endsection

