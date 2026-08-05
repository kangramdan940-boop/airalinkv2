@extends('layouts.app')

@php
    use App\Models\Transaksi\Transfer;
    use App\Models\Transaksi\Tunai;
    use App\Models\Transaksi\Limit;
    use App\Models\User;
    use Carbon\Carbon;

    $mulai = request('mulai') ? Carbon::parse(request('mulai')) : Carbon::today();
    $sampai = request('sampai') ? Carbon::parse(request('sampai'))->endOfDay() : Carbon::today()->endOfDay();

    $users = User::all();
    $currentUserId = auth()->id();

    $karyawanData = [];
    $totalKeuntunganAll = 0;

    foreach ($users as $user) {
        $transfers = Transfer::where('user_id', $user->id)
            ->whereBetween('created_at', [$mulai, $sampai])
            ->get();
        $tunais = Tunai::where('user_id', $user->id)
            ->whereBetween('created_at', [$mulai, $sampai])
            ->get();

        $totalTransfer = $transfers->sum('nominal');
        $totalTunai = $tunais->sum('nominal');

        if ($totalTransfer > 0 || $totalTunai > 0) {
            $adminTransfer = $transfers->sum('admin');
            // Admin fee tunai disimpan di kolom keterangan (legacy)
            $adminTunai = $tunais->sum(function($item) {
                return is_numeric($item->keterangan) ? (double) $item->keterangan : 0;
            });
            $jumlahTransfer = $transfers->count();
            $jumlahTunai = $tunais->count();
            $kunjungan = $jumlahTransfer + $jumlahTunai;
            $keuntungan = $adminTransfer + $adminTunai;
            $totalKeuntunganAll += $keuntungan;

            // Cabang toko (ambil dari data terakhir)
            $cabang = $transfers->last()?->cabang_toko ?? $tunais->last()?->cabang_toko ?? '-';

            $karyawanData[] = [
                'user' => $user,
                'cabang' => $cabang,
                'total_transfer' => $totalTransfer,
                'total_tunai' => $totalTunai,
                'admin_transfer' => $adminTransfer,
                'admin_tunai' => $adminTunai,
                'jumlah_transfer' => $jumlahTransfer,
                'jumlah_tunai' => $jumlahTunai,
                'kunjungan' => $kunjungan,
                'keuntungan' => $keuntungan,
                'is_current' => $user->id == $currentUserId,
            ];
        }
    }

    // Sort: current user first, then yayang
    usort($karyawanData, function($a, $b) {
        if ($a['user']->username === 'yayang') return -1;
        if ($b['user']->username === 'yayang') return 1;
        return $b['is_current'] <=> $a['is_current'];
    });
@endphp

@section('content')

{{-- Filter Tanggal --}}
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-body" style="padding: 1.2rem 1.5rem;">
        <form method="GET" action="{{ url('/kalkulator') }}" style="display: flex; align-items: flex-end; gap: 1.5rem; flex-wrap: wrap;">
            <div>
                <label class="form-label">Mulai Tanggal</label>
                <input type="date" name="mulai" class="form-control" value="{{ $mulai->format('Y-m-d') }}">
            </div>
            <div>
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="sampai" class="form-control" value="{{ $sampai->format('Y-m-d') }}">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Submit
                </button>
            </div>
            {{-- Total Keuntungan --}}
            <div style="margin-left: auto;">
                <div style="background: rgba(255,255,255,0.05); border: 2px solid var(--border); border-radius: 12px; padding: 0.6rem 1.5rem; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 800;">{{ number_format($totalKeuntunganAll, 0, ',', ',') }}</div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Title --}}
<h4 style="font-weight: 800; margin-bottom: 1.5rem;">Kalkulator</h4>

{{-- Cards Karyawan --}}
<div class="calc-grid">
    @foreach($karyawanData as $data)
    @php
        $isYayang = $data['user']->username === 'yayang';
        $isCurrent = $data['is_current'];
        $cardClass = $isYayang ? 'glass-card-yayang' : ($isCurrent ? 'glass-card-current' : 'glass-card');
    @endphp
    <div class="{{ $cardClass }}">
        {{-- Header --}}
        <div style="padding: 1.3rem 1.5rem; position: relative; z-index: 1;">
            <div style="font-weight: 700; font-size: 1.05rem; color: #fff;">
                {{ $data['user']->username }}
            </div>
            <div style="font-size: 0.82rem; color: rgba(255,255,255,0.6);">
                - {{ $data['cabang'] ?: '-' }}
            </div>
            <div style="font-size: 0.78rem; font-weight: 600; margin-top: 0.6rem; color: rgba(255,255,255,0.8); letter-spacing: 0.3px;">
                Detail Transaksi
            </div>
        </div>

        {{-- Detail Table --}}
        <div style="padding: 0 1.5rem 1.5rem; position: relative; z-index: 1;">
            <table class="glass-table">
                <thead>
                    <tr>
                        <th>Total Transfer</th>
                        <th>Total Tarik Tunai</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-weight: 700; font-size: 0.95rem; color: #fff;">{{ number_format($data['total_transfer'], 0, ',', ',') }}</td>
                        <td style="font-weight: 700; font-size: 0.95rem; color: #fff;">{{ number_format($data['total_tunai'], 0, ',', ',') }}</td>
                    </tr>
                    <tr>
                        <td style="color: rgba(255,255,255,0.75);">{{ number_format($data['admin_transfer'], 0, ',', ',') }} ({{ $data['jumlah_transfer'] }})</td>
                        <td style="color: rgba(255,255,255,0.75);">{{ number_format($data['admin_tunai'], 0, ',', ',') }} ({{ $data['jumlah_tunai'] }})</td>
                    </tr>
                    <tr>
                        <td style="color: rgba(255,255,255,0.75);">kunjungan ({{ $data['kunjungan'] }})</td>
                        <td style="font-weight: 600; color: #fff;">keuntung ({{ number_format($data['keuntungan'], 0, ',', ',') }})</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>

{{-- Keuntungan Yayang - Saham 50% --}}
@php
    $yayangData = collect($karyawanData)->firstWhere(fn($d) => $d['user']->username === 'yayang');
@endphp
@if($yayangData && $yayangData['keuntungan'] > 0)
<div style="margin-top: 2rem;">
    <div class="saham-box">
        <div style="padding: 1.8rem 2rem; position: relative; z-index: 1;">
            <div style="font-size: 2.4rem; font-weight: 800; color: #fff; letter-spacing: -1px;">{{ number_format($yayangData['keuntungan'], 0, ',', ',') }}</div>
            <div style="font-size: 0.85rem; color: rgba(255,255,255,0.5); margin: 0.5rem 0;">/ 50%</div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #a5b4fc;">{{ number_format($yayangData['keuntungan'] / 2, 0, ',', ',') }}</div>
        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
    .calc-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.2rem;
        align-items: start;
    }
    @media (max-width: 1200px) {
        .calc-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .calc-grid { grid-template-columns: 1fr; }
    }

    /* Liquid Glass Card */
    .glass-card {
        background: rgba(255, 255, 255, 0.04);
        backdrop-filter: blur(40px) saturate(180%);
        -webkit-backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        box-shadow:
            0 8px 32px rgba(0, 0, 0, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.06);
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .glass-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
    }
    .glass-card:hover {
        transform: translateY(-3px);
        border-color: rgba(255, 255, 255, 0.12);
        box-shadow:
            0 16px 48px rgba(0, 0, 0, 0.3),
            inset 0 1px 0 rgba(255, 255, 255, 0.1);
    }

    /* Yayang - Blue Liquid Glass */
    .glass-card-yayang {
        background: linear-gradient(135deg, rgba(59, 59, 239, 0.3), rgba(99, 102, 241, 0.15));
        backdrop-filter: blur(40px) saturate(200%);
        -webkit-backdrop-filter: blur(40px) saturate(200%);
        border: 1px solid rgba(99, 102, 241, 0.3);
        border-radius: 20px;
        box-shadow:
            0 8px 32px rgba(59, 59, 239, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.1);
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .glass-card-yayang::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(165, 180, 252, 0.4), transparent);
    }
    .glass-card-yayang::after {
        content: '';
        position: absolute;
        top: -50%; right: -50%;
        width: 100%; height: 100%;
        background: radial-gradient(circle, rgba(139, 92, 246, 0.1), transparent);
        border-radius: 50%;
    }
    .glass-card-yayang:hover {
        transform: translateY(-3px);
        border-color: rgba(139, 92, 246, 0.4);
        box-shadow:
            0 16px 48px rgba(59, 59, 239, 0.35),
            inset 0 1px 0 rgba(255, 255, 255, 0.15);
    }

    /* Current user - Teal Glass */
    .glass-card-current {
        background: linear-gradient(135deg, rgba(13, 148, 136, 0.25), rgba(20, 184, 166, 0.1));
        backdrop-filter: blur(40px) saturate(200%);
        -webkit-backdrop-filter: blur(40px) saturate(200%);
        border: 1px solid rgba(20, 184, 166, 0.3);
        border-radius: 20px;
        box-shadow:
            0 8px 32px rgba(13, 148, 136, 0.15),
            inset 0 1px 0 rgba(255, 255, 255, 0.08);
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .glass-card-current::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(94, 234, 212, 0.3), transparent);
    }
    .glass-card-current:hover {
        transform: translateY(-3px);
        border-color: rgba(20, 184, 166, 0.4);
        box-shadow:
            0 16px 48px rgba(13, 148, 136, 0.3),
            inset 0 1px 0 rgba(255, 255, 255, 0.12);
    }

    /* Glass Table inside cards */
    .glass-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        overflow: hidden;
    }
    .glass-table th {
        padding: 0.6rem 0.8rem;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: rgba(255, 255, 255, 0.5);
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        background: rgba(0, 0, 0, 0.15);
    }
    .glass-table td {
        padding: 0.6rem 0.8rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        font-size: 0.85rem;
    }
    .glass-table tr:last-child td { border-bottom: none; }

    /* Saham Box */
    .saham-box {
        background: linear-gradient(135deg, rgba(59, 59, 239, 0.25), rgba(139, 92, 246, 0.15));
        backdrop-filter: blur(40px) saturate(180%);
        -webkit-backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid rgba(139, 92, 246, 0.3);
        border-radius: 20px;
        box-shadow:
            0 8px 32px rgba(59, 59, 239, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.08);
        max-width: 380px;
        position: relative;
        overflow: hidden;
    }
    .saham-box::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(165, 180, 252, 0.4), transparent);
    }
</style>
@endpush
