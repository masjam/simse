<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Siswa - {{ $user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 14px;
            font-weight: normal;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 5px;
            margin-top: 20px;
            margin-bottom: 10px;
            border-left: 4px solid #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .table-data td {
            padding: 4px 8px;
            vertical-align: top;
        }
        .table-data td.label {
            width: 30%;
            font-weight: bold;
        }
        .table-data td.colon {
            width: 2%;
        }
        .table-list {
            border: 1px solid #ccc;
        }
        .table-list th, .table-list td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }
        .table-list th {
            background-color: #f9f9f9;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        .btn-print {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="text-align: right;">
        <button class="btn-print" onclick="window.print()">Cetak Profil</button>
    </div>

    <div class="header">
        <h1>PROFIL PESERTA DIDIK</h1>
        <h2>Sistem Informasi Sekolah</h2>
    </div>

    @php $student = $user->student; @endphp

    <div class="section-title">A. DATA PRIBADI</div>
    <table class="table-data">
        <tr><td class="label">Nama Lengkap</td><td class="colon">:</td><td>{{ $user->name }}</td></tr>
        <tr><td class="label">NISN</td><td class="colon">:</td><td>{{ $student?->nisn }}</td></tr>
        <tr><td class="label">NIK</td><td class="colon">:</td><td>{{ $student?->nik }}</td></tr>
        <tr><td class="label">Jenis Kelamin</td><td class="colon">:</td><td>{{ $student?->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
        <tr><td class="label">Kelas / Rombel</td><td class="colon">:</td><td>{{ $student?->studentClass?->name }} (Tingkat {{ $student?->studentClass?->tingkat?->name }})</td></tr>
        <tr><td class="label">Tempat, Tanggal Lahir</td><td class="colon">:</td><td>{{ $student?->tempat_lahir }}, {{ $student?->tanggal_lahir ? date('d-m-Y', strtotime($student->tanggal_lahir)) : '' }}</td></tr>
        <tr><td class="label">Agama</td><td class="colon">:</td><td>{{ $student?->agama }}</td></tr>
        <tr><td class="label">Kewarganegaraan</td><td class="colon">:</td><td>{{ $student?->kewarganegaraan }}</td></tr>
        <tr><td class="label">No Registrasi Akta</td><td class="colon">:</td><td>{{ $student?->no_registrasi_akta_lahir }}</td></tr>
        <tr><td class="label">Anak ke-</td><td class="colon">:</td><td>{{ $student?->anak_ke }}</td></tr>
        <tr><td class="label">Tempat Tinggal</td><td class="colon">:</td><td>{{ $student?->tempat_tinggal }}</td></tr>
        <tr><td class="label">Moda Transportasi</td><td class="colon">:</td><td>{{ $student?->moda_transportasi }}</td></tr>
    </table>

    <div class="section-title">B. KONTAK & ALAMAT</div>
    <table class="table-data">
        <tr><td class="label">Alamat Jalan</td><td class="colon">:</td><td>{{ $student?->alamat_jalan }}</td></tr>
        <tr><td class="label">RT / RW</td><td class="colon">:</td><td>{{ $student?->rt }} / {{ $student?->rw }}</td></tr>
        <tr><td class="label">Dusun</td><td class="colon">:</td><td>{{ $student?->dusun }}</td></tr>
        <tr><td class="label">Desa / Kelurahan</td><td class="colon">:</td><td>{{ $student?->desa_kelurahan }}</td></tr>
        <tr><td class="label">Kode Pos</td><td class="colon">:</td><td>{{ $student?->kode_pos }}</td></tr>
        <tr><td class="label">No. Telepon Rumah</td><td class="colon">:</td><td>{{ $student?->no_telepon_rumah }}</td></tr>
        <tr><td class="label">No. HP</td><td class="colon">:</td><td>{{ $student?->no_hp }}</td></tr>
    </table>

    <div class="section-title">C. DATA ORANG TUA / WALI</div>
    <table class="table-data">
        <tr><td class="label"><strong>Ayah Kandung</strong></td><td class="colon"></td><td></td></tr>
        <tr><td class="label">Nama Ayah</td><td class="colon">:</td><td>{{ $student?->nama_ayah }}</td></tr>
        <tr><td class="label">NIK Ayah</td><td class="colon">:</td><td>{{ $student?->nik_ayah }}</td></tr>
        <tr><td class="label">Tahun Lahir</td><td class="colon">:</td><td>{{ $student?->tahun_lahir_ayah }}</td></tr>
        <tr><td class="label">Pendidikan</td><td class="colon">:</td><td>{{ $student?->pendidikan_ayah }}</td></tr>
        <tr><td class="label">Pekerjaan</td><td class="colon">:</td><td>{{ $student?->pekerjaan_ayah }}</td></tr>
        <tr><td class="label">Penghasilan</td><td class="colon">:</td><td>{{ $student?->penghasilan_ayah }}</td></tr>
        
        <tr><td colspan="3"><hr style="border: 0; border-top: 1px dashed #ccc; margin: 10px 0;"></td></tr>
        
        <tr><td class="label"><strong>Ibu Kandung</strong></td><td class="colon"></td><td></td></tr>
        <tr><td class="label">Status Hidup</td><td class="colon">:</td><td>{{ $student?->status_hidup_ibu }}</td></tr>
        <tr><td class="label">Nama Ibu</td><td class="colon">:</td><td>{{ $student?->nama_ibu }}</td></tr>
        <tr><td class="label">NIK Ibu</td><td class="colon">:</td><td>{{ $student?->nik_ibu }}</td></tr>
        <tr><td class="label">Tahun Lahir</td><td class="colon">:</td><td>{{ $student?->tahun_lahir_ibu }}</td></tr>
        <tr><td class="label">Pendidikan</td><td class="colon">:</td><td>{{ $student?->pendidikan_ibu }}</td></tr>
        <tr><td class="label">Pekerjaan</td><td class="colon">:</td><td>{{ $student?->pekerjaan_ibu }}</td></tr>
        <tr><td class="label">Penghasilan</td><td class="colon">:</td><td>{{ $student?->penghasilan_ibu }}</td></tr>
        
        @if($student?->mempunyai_wali)
        <tr><td colspan="3"><hr style="border: 0; border-top: 1px dashed #ccc; margin: 10px 0;"></td></tr>
        <tr><td class="label"><strong>Wali</strong></td><td class="colon"></td><td></td></tr>
        <tr><td class="label">Nama Wali</td><td class="colon">:</td><td>{{ $student?->nama_wali }}</td></tr>
        <tr><td class="label">NIK Wali</td><td class="colon">:</td><td>{{ $student?->nik_wali }}</td></tr>
        <tr><td class="label">Tahun Lahir</td><td class="colon">:</td><td>{{ $student?->tahun_lahir_wali }}</td></tr>
        <tr><td class="label">Pendidikan</td><td class="colon">:</td><td>{{ $student?->pendidikan_wali }}</td></tr>
        <tr><td class="label">Pekerjaan</td><td class="colon">:</td><td>{{ $student?->pekerjaan_wali }}</td></tr>
        <tr><td class="label">Penghasilan</td><td class="colon">:</td><td>{{ $student?->penghasilan_wali }}</td></tr>
        @endif
    </table>

    <div class="section-title">D. PRESTASI SISWA</div>
    <table class="table-list">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Prestasi / Lomba</th>
                <th>Tingkat</th>
                <th>Tahun</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($student?->achievements ?? [] as $index => $ach)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $ach->name }}</td>
                <td>{{ $ach->level }}</td>
                <td>{{ $ach->year }}</td>
                <td>{{ $ach->description }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; font-style: italic;">Tidak ada data prestasi</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">E. DATA PERIODIK</div>
    <table class="table-list">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Catat</th>
                <th>Tinggi Badan (cm)</th>
                <th>Berat Badan (kg)</th>
                <th>Lingkar Kepala (cm)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($student?->periodics ?? [] as $index => $per)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $per->recorded_at ? date('d-m-Y', strtotime($per->recorded_at)) : '' }}</td>
                <td style="text-align: center;">{{ $per->tinggi_badan }}</td>
                <td style="text-align: center;">{{ $per->berat_badan }}</td>
                <td style="text-align: center;">{{ $per->lingkar_kepala }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; font-style: italic;">Tidak ada data periodik</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
