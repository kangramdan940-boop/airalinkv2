<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan</title>
    <style>
        @page { margin: 25mm 20mm 20mm 20mm; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            color: #333;
            line-height: 1.4;
        }

        /* Header */
        .header {
            text-align: center;
            padding-bottom: 12px;
            margin-bottom: 20px;
            border-bottom: 3px solid #2563eb;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            color: #1e293b;
            margin: 0 0 2px 0;
            letter-spacing: 1px;
        }
        .header .company {
            font-size: 10pt;
            color: #64748b;
            margin-bottom: 6px;
        }
        .header .period {
            display: inline-block;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 4px 14px;
            border-radius: 4px;
            font-size: 9pt;
            color: #1d4ed8;
            font-weight: bold;
        }
        .header .filter {
            font-size: 8pt;
            color: #64748b;
            margin-top: 4px;
        }

        /* Section */
        .section {
            margin-bottom: 18px;
        }
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #1e293b;
            padding: 6px 10px;
            background: #f8fafc;
            border-left: 3px solid #2563eb;
            margin-bottom: 8px;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #1e293b;
            color: #fff;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 7px 10px;
            text-align: left;
        }
        td {
            padding: 6px 10px;
            font-size: 8.5pt;
            border-bottom: 1px solid #e2e8f0;
        }
        tr:nth-child(even) td {
            background: #f8fafc;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }

        .text-green { color: #059669; }
        .text-red { color: #dc2626; }
        .text-blue { color: #2563eb; }

        .row-total td {
            background: #f1f5f9 !important;
            font-weight: bold;
            border-top: 2px solid #cbd5e1;
            border-bottom: none;
        }

        /* Summary Cards */
        .summary {
            width: 100%;
            margin-bottom: 18px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
        }
        .summary td {
            width: 33.33%;
            text-align: center;
            padding: 10px;
            border-bottom: none;
            border-right: 1px solid #e2e8f0;
        }
        .summary td:last-child { border-right: none; }
        .summary .label {
            font-size: 7pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .summary .value {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 2px;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7pt;
            color: #94a3b8;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
        }

        .page-break { page-break-before: always; }
    </style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <h1>LAPORAN KEUANGAN</h1>
    <div class="company">{{ config('app.name') }}</div>
    <div class="period">{{ $bulanMulai }} — {{ $bulanAkhir }}</div>
    <div class="filter">Filter: {{ $filterUsername }}</div>
</div>

{{-- Summary --}}
<table class="summary">
    <tr>
        <td>
            <div class="label">Total Keuntungan</div>
            <div class="value text-blue">Rp {{ number_format($grandTotalKeuntungan, 0, ',', '.') }}</div>
        </td>
        <td>
            <div class="label">Total Pengeluaran</div>
            <div class="value text-red">Rp {{ number_format($grandTotalPengeluaran, 0, ',', '.') }}</div>
        </td>
        <td>
            <div class="label">Keuntungan Bersih</div>
            <div class="value {{ $grandTotalBersih >= 0 ? 'text-green' : 'text-red' }}">Rp {{ number_format($grandTotalBersih, 0, ',', '.') }}</div>
        </td>
    </tr>
</table>

{{-- Rekap Bulanan --}}
<div class="section">
    <div class="section-title">Rekap Per Bulan</div>
    <table>
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:30%">Bulan</th>
                <th class="text-right" style="width:22%">Keuntungan</th>
                <th class="text-right" style="width:22%">Pengeluaran</th>
                <th class="text-right" style="width:21%">Bersih</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporanBulanan as $i => $data)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $data['label'] }}</td>
                <td class="text-right">Rp {{ number_format($data['total_keuntungan'], 0, ',', '.') }}</td>
                <td class="text-right text-red">Rp {{ number_format($data['total_pengeluaran'], 0, ',', '.') }}</td>
                <td class="text-right bold {{ $data['total_bersih'] >= 0 ? 'text-green' : 'text-red' }}">Rp {{ number_format($data['total_bersih'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="row-total">
                <td colspan="2">TOTAL</td>
                <td class="text-right">Rp {{ number_format($grandTotalKeuntungan, 0, ',', '.') }}</td>
                <td class="text-right text-red">Rp {{ number_format($grandTotalPengeluaran, 0, ',', '.') }}</td>
                <td class="text-right {{ $grandTotalBersih >= 0 ? 'text-green' : 'text-red' }}">Rp {{ number_format($grandTotalBersih, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- Detail Per Karyawan --}}
<div class="section">
    <div class="section-title">Detail Per Karyawan</div>
    @foreach($laporanBulanan as $data)
        @if(count($data['users']) > 0)
        <div style="margin-bottom: 6px; font-size: 8pt; font-weight: bold; color: #475569;">{{ $data['label'] }}</div>
        <table style="margin-bottom: 12px;">
            <thead>
                <tr>
                    <th style="width:5%">No</th>
                    <th style="width:50%">Karyawan</th>
                    <th class="text-right" style="width:45%">Keuntungan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['users'] as $j => $user)
                <tr>
                    <td class="text-center">{{ $j + 1 }}</td>
                    <td>{{ $user['username'] }}</td>
                    <td class="text-right bold">Rp {{ number_format($user['keuntungan'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="row-total">
                    <td colspan="2">Subtotal</td>
                    <td class="text-right">Rp {{ number_format($data['total_keuntungan'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        @endif
    @endforeach
</div>

{{-- Bagi Hasil Investor --}}
@if(count($investorReport) > 0)
<div class="section">
    <div class="section-title">Bagi Hasil Investor</div>
    <table>
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:18%">Investor</th>
                <th class="text-center" style="width:8%">%</th>
                <th class="text-right" style="width:18%">Keuntungan</th>
                <th class="text-right" style="width:18%">Pengeluaran</th>
                <th class="text-right" style="width:16%">Bersih</th>
                <th class="text-right" style="width:17%">Bagi Hasil</th>
            </tr>
        </thead>
        <tbody>
            @php $totalBagi = 0; @endphp
            @foreach($investorReport as $i => $inv)
            @php $totalBagi += $inv['bagi_hasil']; @endphp
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $inv['username'] }}</td>
                <td class="text-center text-blue bold">{{ $inv['persentase'] }}%</td>
                <td class="text-right">{{ $inv['keuntungan'] > 0 ? 'Rp ' . number_format($inv['keuntungan'], 0, ',', '.') : '-' }}</td>
                <td class="text-right text-red">{{ $inv['keuntungan'] > 0 ? 'Rp ' . number_format($inv['pengeluaran'], 0, ',', '.') : '-' }}</td>
                <td class="text-right bold {{ $inv['bersih'] >= 0 ? 'text-green' : 'text-red' }}">{{ $inv['keuntungan'] > 0 ? 'Rp ' . number_format($inv['bersih'], 0, ',', '.') : '-' }}</td>
                <td class="text-right text-green bold">{{ $inv['keuntungan'] > 0 ? 'Rp ' . number_format($inv['bagi_hasil'], 0, ',', '.') : '-' }}</td>
            </tr>
            @endforeach
            <tr class="row-total">
                <td colspan="6">TOTAL BAGI HASIL</td>
                <td class="text-right text-green">Rp {{ number_format($totalBagi, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</div>
@endif

{{-- Footer --}}
<div class="footer">
    Dicetak: {{ $tanggalCetak }} &nbsp;|&nbsp; {{ config('app.name') }}
</div>

</body>
</html>
