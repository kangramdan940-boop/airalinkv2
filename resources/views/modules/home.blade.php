@extends('layouts.app')

@php
    use App\Models\Transaksi\Transfer;
    use App\Models\Transaksi\Tunai;
    use App\Models\Transaksi\Limit;
    use App\Models\Transaksi\Tabungan;
    use App\Models\Transaksi\UangCashMonitoring;
    use App\Models\Transaksi\UangCash;
    use App\Models\User;
    use Carbon\Carbon;

    $today = Carbon::today();
    $users = User::all();

    $karyawanAktif = [];
    $karyawanTidakAktif = [];

    foreach ($users as $user) {
        $transfers = Transfer::where('user_id', $user->id)->whereDate('created_at', $today)->get();
        $tunais = Tunai::where('user_id', $user->id)->whereDate('created_at', $today)->get();

        $totalTransfer = $transfers->sum('nominal');
        $totalTunai = $tunais->sum('nominal');

        if ($totalTransfer > 0 || $totalTunai > 0) {
            // Uang Ketika Buka = sum(nominal * total) dari uang-cash-monitoring hari ini
            $monitorings = UangCashMonitoring::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->get();
            $uangBuka = $monitorings->sum(fn($i) => $i->nominal * $i->total);

            // Uang Closing = sum(nominal * total) dari uang-cash (input closing karyawan)
            $cashClosing = UangCash::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->get();
            $uangClosing = $cashClosing->sum(fn($i) => $i->nominal * $i->total);
            $uangSisa = $uangBuka + $totalTransfer - $totalTunai;
            $selisih = $uangSisa - $uangClosing;

            $karyawanAktif[] = [
                'user' => $user,
                'uang_buka' => $uangBuka,
                'uang_closing' => $uangClosing,
                'uang_sisa' => $uangSisa,
                'selisih' => $selisih,
                'total_transfer' => $totalTransfer,
                'total_tunai' => $totalTunai,
            ];
        } else {
            $karyawanTidakAktif[] = $user;
        }
    }

    $totalBuka = collect($karyawanAktif)->sum('uang_buka');
    $totalClosing = collect($karyawanAktif)->sum('uang_closing');
    $totalSisa = collect($karyawanAktif)->sum('uang_sisa');
@endphp

@section('content')

{{-- Title --}}
<div style="text-align: center; margin-bottom: 2.5rem;">
    <h2 style="font-size: 1.5rem; font-weight: 800; letter-spacing: -0.5px;">
        LAPORAN PERTANGGUNG JAWABAN TOKO
    </h2>
</div>

{{-- Section 1: Rekap Cash --}}
<div style="margin-bottom: 2.5rem;">
    <div style="font-size: 0.85rem; font-weight: 600; color: #94a3b8; margin-bottom: 1rem;">
        Rekap Cash — Hanya Karyawan yang Bertransaksi Hari Ini
    </div>
    <div class="card">
        <div class="card-body" style="padding: 0; overflow-x: auto;">
            <table class="table" style="margin: 0; width: 100%; table-layout: fixed;">
                <thead>
                    <tr>
                        <th style="width: 16%;">Nama</th>
                        <th style="width: 21%; color: #ef4444;">Uang Ketika Buka</th>
                        <th style="width: 21%; color: #ef4444;">Uang Closing</th>
                        <th style="width: 21%; color: #ef4444;">Uang Sisa Seharusnya</th>
                        <th style="width: 21%;">Selisih</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($karyawanAktif as $data)
                    <tr>
                        <td style="color: #f1f5f9; font-weight: 500;">{{ $data['user']->username }}</td>
                        <td style="color: #ef4444;">{{ number_format($data['uang_buka'], 0, ',', ',') }}</td>
                        <td style="color: #ef4444;">{{ number_format($data['uang_closing'], 0, ',', ',') }}</td>
                        <td style="color: #ef4444;">{{ number_format($data['uang_sisa'], 0, ',', ',') }}</td>
                        <td style="color: {{ $data['selisih'] >= 0 ? '#10b981' : '#ef4444' }}; font-weight: 700;">
                            {{ number_format($data['selisih'], 0, ',', ',') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                            Belum ada karyawan yang bertransaksi hari ini
                        </td>
                    </tr>
                    @endforelse

                    @if(count($karyawanAktif) > 0)
                    <tr style="background: rgba(255,255,255,0.02); border-top: 2px solid var(--border);">
                        <td style="font-weight: 700;">TOTAL</td>
                        <td style="color: #ef4444; font-weight: 700;">{{ number_format($totalBuka, 0, ',', ',') }}</td>
                        <td style="color: #ef4444; font-weight: 700;">{{ number_format($totalClosing, 0, ',', ',') }}</td>
                        <td style="color: #ef4444; font-weight: 700;">{{ number_format($totalSisa, 0, ',', ',') }}</td>
                        <td style="font-weight: 700;">-</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Section 2: Karyawan dengan Transaksi --}}
@if(count($karyawanAktif) > 0)
<div style="margin-bottom: 2.5rem;">
    <div style="font-size: 0.85rem; font-weight: 600; color: #94a3b8; margin-bottom: 1rem;">
        Karyawan dengan Transaksi Hari Ini
    </div>
    <div class="karyawan-grid">
        @foreach($karyawanAktif as $data)
        <div class="card">
            <div style="padding: 1rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.06);">
                <span style="font-weight: 700; font-size: 0.9rem; color: #f1f5f9;">{{ $data['user']->username }}</span>
            </div>
            <div style="padding: 1rem 1.25rem;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="font-size: 0.72rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; padding-bottom: 0.5rem;">Total Transfer</td>
                        <td style="font-size: 0.72rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; padding-bottom: 0.5rem;">Total Tarik Tunai</td>
                    </tr>
                    <tr>
                        <td style="font-size: 1.1rem; font-weight: 700; color: #fff;">{{ number_format($data['total_transfer'], 0, ',', ',') }}</td>
                        <td style="font-size: 1.1rem; font-weight: 700; color: #fff;">{{ number_format($data['total_tunai'], 0, ',', ',') }}</td>
                    </tr>
                </table>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Section 3: Karyawan TANPA Transaksi --}}
@if(count($karyawanTidakAktif) > 0)
<div>
    <div style="font-size: 0.85rem; font-weight: 600; color: #94a3b8; margin-bottom: 1rem;">
        Karyawan TANPA Transaksi Hari Ini
    </div>
    <div class="karyawan-idle-grid">
        @foreach($karyawanTidakAktif as $user)
        <div class="card">
            <div style="padding: 1rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.06);">
                <span style="font-weight: 700; font-size: 0.9rem; color: #f1f5f9;">{{ $user->username }}</span>
            </div>
            <div style="padding: 1rem 1.25rem;">
                <p style="font-size: 0.8rem; color: #64748b; margin: 0;">
                    Tidak ada transaksi (Transfer/Tarik Tunai) untuk hari ini.
                </p>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
    .karyawan-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }
    .karyawan-idle-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
    }
    @media (max-width: 1200px) {
        .karyawan-grid { grid-template-columns: repeat(2, 1fr); }
        .karyawan-idle-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 768px) {
        .karyawan-grid { grid-template-columns: 1fr; }
        .karyawan-idle-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endpush
