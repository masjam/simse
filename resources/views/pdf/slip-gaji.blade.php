@extends('pdf.layout')

@section('content')
<div class="doc-title">
    <h2>SLIP HONORARIUM & GAJI PEGAWAI</h2>
    <p>Periode: {{ $month ?? 'November' }} {{ $year ?? '2026' }} | Status: Resmi / Dicairkan</p>
</div>

<!-- Informasi Pegawai -->
<table style="width: 100%; border-collapse: collapse; font-size: 9.5pt; margin-bottom: 12px;">
    <tr>
        <td style="width: 18%; font-weight: bold; padding: 3px 0;">Nama Pegawai / Guru</td>
        <td style="width: 2%;">:</td>
        <td style="width: 35%; padding: 3px 0;"><strong>{{ $employeeName }}</strong></td>
        <td style="width: 18%; font-weight: bold; padding: 3px 0;">NIP / NBM</td>
        <td style="width: 2%;">:</td>
        <td style="width: 25%; padding: 3px 0;">{{ $employeeNip ?? '-' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold; padding: 3px 0;">Jabatan / Posisi</td>
        <td>:</td>
        <td style="padding: 3px 0;">{{ $position ?? 'Guru Pengajar' }}</td>
        <td style="font-weight: bold; padding: 3px 0;">Metode Pembayaran</td>
        <td>:</td>
        <td style="padding: 3px 0;">Transfer Bank Syariah</td>
    </tr>
</table>

<!-- Rincian Komponen Gaji: Penerimaan vs Potongan -->
<table style="width: 100%; border-collapse: collapse; font-size: 9pt;">
    <tr>
        <td style="width: 49%; vertical-align: top;">
            <table class="data-table" style="margin: 0;">
                <thead>
                    <tr>
                        <th colspan="2" style="background-color: #ecfdf5; color: #065f46;">A. PENERIMAAN / PENDAPATAN</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalIncome = 0; @endphp
                    @forelse($earnings as $item)
                        @php $totalIncome += $item['amount']; @endphp
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td class="text-right" style="width: 38%;">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td>Gaji Pokok / Honorarium</td>
                            <td class="text-right">Rp {{ number_format($basicSalary ?? 3500000, 0, ',', '.') }}</td>
                        </tr>
                        @php $totalIncome = $basicSalary ?? 3500000; @endphp
                    @endforelse
                    <tr style="background-color: #f3f4f6; font-weight: bold;">
                        <td>TOTAL PENERIMAAN</td>
                        <td class="text-right">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </td>
        <td style="width: 2%;"></td>
        <td style="width: 49%; vertical-align: top;">
            <table class="data-table" style="margin: 0;">
                <thead>
                    <tr>
                        <th colspan="2" style="background-color: #fef2f2; color: #991b1b;">B. POTONGAN</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalDeduction = 0; @endphp
                    @forelse($deductions as $item)
                        @php $totalDeduction += $item['amount']; @endphp
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td class="text-right" style="width: 38%;">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td>Iuran Paguyuban & BPJS</td>
                            <td class="text-right">Rp {{ number_format($defaultDeduction ?? 150000, 0, ',', '.') }}</td>
                        </tr>
                        @php $totalDeduction = $defaultDeduction ?? 150000; @endphp
                    @endforelse
                    <tr style="background-color: #f3f4f6; font-weight: bold;">
                        <td>TOTAL POTONGAN</td>
                        <td class="text-right">Rp {{ number_format($totalDeduction, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>

<!-- Take Home Pay Box -->
<div style="margin-top: 14px; padding: 10px 16px; background-color: #f0fdf4; border: 1.5px solid #059669; text-align: right;">
    <span style="font-size: 11pt; font-weight: bold; color: #065f46;">PENGHASILAN BERSIH (TAKE-HOME PAY):</span>
    <span style="font-size: 14pt; font-weight: 800; color: #047857; margin-left: 12px;">
        Rp {{ number_format($totalIncome - $totalDeduction, 0, ',', '.') }}
    </span>
</div>

<table class="signatures-table" style="margin-top: 28px;">
    <tr>
        <td style="width: 50%;">
            Wonosari, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
            <strong>Bendahara Sekolah</strong>
            <div style="height: 60px;"></div>
            <strong style="text-decoration: underline;">{{ $treasurerName ?? 'Bendahara Sekolah' }}</strong><br>
            <span>NBM. 1198273</span>
        </td>
        <td style="width: 50%;">
            Mengetahui,<br>
            <strong>Kepala Sekolah</strong>
            <div style="height: 60px;"></div>
            <strong style="text-decoration: underline;">{{ $school['principal'] }}</strong><br>
            <span>{{ $school['principal_nip'] }}</span>
        </td>
    </tr>
</table>
@endsection

