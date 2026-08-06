@extends('layouts.app')

@php
    use App\Models\Transaksi\UangCash;
    use App\Models\Transaksi\UangCashMonitoring;
    use App\Models\User;
    use Carbon\Carbon;

    $userId = auth()->id();
    $isAdmin = auth()->user()->hasRole('admin');
    $today = Carbon::today();

    // Modal yang diberikan hari ini (dari monitoring)
    $modalHariIni = UangCashMonitoring::where('user_id', $userId)
        ->whereDate('created_at', $today)
        ->orderBy('nominal', 'desc')
        ->get();
    $totalModal = $modalHariIni->sum(fn($i) => $i->nominal * $i->total);

    // Data closing uang cash hari ini (yang user input)
    $cashHariIni = UangCash::where('user_id', $userId)
        ->whereDate('created_at', $today)
        ->orderBy('nominal', 'desc')
        ->get();
    $totalCash = $cashHariIni->sum(fn($i) => $i->nominal * $i->total);

    // Admin: data closing semua karyawan
    $closingPerUser = [];
    if ($isAdmin) {
        $allUsers = User::role(['admin', 'user'])->get();
        foreach ($allUsers as $u) {
            $userCash = UangCash::where('user_id', $u->id)
                ->whereDate('created_at', $today)
                ->orderBy('nominal', 'desc')
                ->get();
            $userModal = UangCashMonitoring::where('user_id', $u->id)
                ->whereDate('created_at', $today)
                ->get();
            $totalUserCash = $userCash->sum(fn($i) => $i->nominal * $i->total);
            $totalUserModal = $userModal->sum(fn($i) => $i->nominal * $i->total);

            if ($totalUserCash > 0 || $totalUserModal > 0) {
                $closingPerUser[] = [
                    'user' => $u,
                    'items' => $userCash,
                    'total_cash' => $totalUserCash,
                    'total_modal' => $totalUserModal,
                ];
            }
        }
    }
@endphp

@section('content')

{{-- Info Modal Hari Ini --}}
<div class="card" style="margin-bottom: 1.5rem; border-color: rgba(16,185,129,0.2);">
    <div class="card-header" style="background: rgba(16,185,129,0.05);">
        <h5 style="font-size: 0.9rem;">
            <i class="bi bi-wallet2" style="color: #10b981; margin-right: 0.4rem;"></i> Modal Uang Cash Hari Ini
        </h5>
        <span style="font-size: 1.1rem; font-weight: 800; color: #34d399;">Rp {{ number_format($totalModal, 0, ',', '.') }}</span>
    </div>
    @if($modalHariIni->count() > 0)
    <div class="card-body" style="padding: 0.8rem 1.5rem;">
        <div style="display: flex; flex-wrap: wrap; gap: 0.8rem;">
            @foreach($modalHariIni as $m)
            <div style="padding: 0.5rem 1rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 8px;">
                <span style="color: #94a3b8; font-size: 0.75rem;">Rp {{ number_format($m->nominal, 0, ',', '.') }}</span>
                <span style="color: #fbbf24; font-weight: 700; margin-left: 0.3rem;">× {{ $m->total }}</span>
                <span style="color: #64748b; margin-left: 0.3rem; font-size: 0.75rem;">= Rp {{ number_format($m->nominal * $m->total, 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="card-body" style="padding: 1rem 1.5rem;">
        <p style="color: #64748b; margin: 0; font-size: 0.85rem;">Belum ada modal yang diberikan hari ini.</p>
    </div>
    @endif
</div>

{{-- Closing Form + Data --}}
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    
    {{-- Left: Form Input --}}
    <div class="card">
        <div class="card-header">
            <h5 style="font-size: 0.9rem;"><i class="bi bi-plus-circle" style="color: var(--primary); margin-right: 0.4rem;"></i> Input Uang Cash Closing</h5>
        </div>
        <div class="card-body">
            <form id="cashForm" method="POST" action="{{ url('uang-cash') }}">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">Pecahan <span class="text-danger">*</span></label>
                        <select name="nominal" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <option value="1000">Rp 1.000</option>
                            <option value="2000">Rp 2.000</option>
                            <option value="5000">Rp 5.000</option>
                            <option value="10000">Rp 10.000</option>
                            <option value="20000">Rp 20.000</option>
                            <option value="50000">Rp 50.000</option>
                            <option value="100000">Rp 100.000</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">Jumlah Lembar <span class="text-danger">*</span></label>
                        <input type="number" name="total" class="form-control" placeholder="0" required min="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="color: #cbd5e1;">Cabang / Ruko</label>
                    @if(auth()->user()->hasRole('admin'))
                    <select name="cabang_toko" class="form-select">
                        <option value="1">Ruko 1</option>
                        <option value="2">Ruko 2</option>
                        <option value="3">Ruko 3</option>
                        <option value="4">Ruko 4</option>
                        <option value="5">Ruko 5</option>
                    </select>
                    @else
                    <input type="text" class="form-control" value="{{ auth()->user()->username }}" disabled style="background: rgba(255,255,255,0.06) !important; color: #94a3b8 !important; -webkit-text-fill-color: #94a3b8 !important; opacity: 1 !important;">
                    <input type="hidden" name="cabang_toko" value="{{ auth()->user()->username }}">
                    @endif
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class="bi bi-check-lg"></i> Simpan
                </button>
            </form>
        </div>
    </div>

    {{-- Right: Data Closing Hari Ini --}}
    <div class="card">
        <div class="card-header">
            <h5 style="font-size: 0.9rem;"><i class="bi bi-cash-stack" style="color: #fbbf24; margin-right: 0.4rem;"></i> Cash Closing Hari Ini</h5>
            <span style="font-size: 0.9rem; font-weight: 700; color: #34d399;">Rp {{ number_format($totalCash, 0, ',', '.') }}</span>
        </div>
        <div class="card-body" style="padding: 0;">
            @if($cashHariIni->count() > 0)
            <table class="table" style="margin: 0; width: 100%;">
                <thead>
                    <tr>
                        <th style="color: #94a3b8;">Pecahan</th>
                        <th style="text-align: center; color: #94a3b8;">Jml</th>
                        <th style="text-align: right; color: #94a3b8;">Subtotal</th>
                        <th style="text-align: center; color: #94a3b8; width: 50px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cashHariIni as $item)
                    <tr>
                        <td style="color: #f1f5f9;">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                        <td style="text-align: center; color: #fbbf24; font-weight: 600;">{{ $item->total }}</td>
                        <td style="text-align: right; color: #f1f5f9; font-weight: 600;">Rp {{ number_format($item->nominal * $item->total, 0, ',', '.') }}</td>
                        <td style="text-align: center;">
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteCash({{ $item->id }})" title="Hapus">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: rgba(0,0,0,0.1);">
                        <td style="font-weight: 700; color: #f1f5f9;">Total</td>
                        <td style="text-align: center; font-weight: 700; color: #fbbf24;">{{ $cashHariIni->sum('total') }}</td>
                        <td style="text-align: right; font-weight: 700; color: #34d399;">Rp {{ number_format($totalCash, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            @else
            <div style="padding: 3rem; text-align: center; color: #64748b;">
                <i class="bi bi-inbox" style="font-size: 1.5rem; display: block; margin-bottom: 0.5rem;"></i>
                Belum ada input closing hari ini
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Admin: Closing Semua Karyawan --}}
@if($isAdmin && count($closingPerUser) > 0)
<div style="margin-top: 2rem;">
    <div style="font-size: 0.85rem; font-weight: 600; color: #94a3b8; margin-bottom: 1rem;">
        <i class="bi bi-people-fill" style="margin-right: 0.3rem;"></i> Uang Closing Karyawan Hari Ini
    </div>
    <div class="closing-grid">
        @foreach($closingPerUser as $data)
        <div class="card">
            <div class="card-header">
                <h5 style="font-size: 0.9rem;">
                    <i class="bi bi-person-circle" style="color: #60a5fa; margin-right: 0.4rem;"></i>
                    {{ $data['user']->username }}
                </h5>
                <div style="text-align: right;">
                    <div style="font-size: 0.7rem; color: #94a3b8;">Modal / Closing</div>
                    <span style="font-size: 0.82rem; font-weight: 700; color: #34d399;">{{ number_format($data['total_modal'], 0, ',', '.') }}</span>
                    <span style="color: #64748b; margin: 0 0.2rem;">/</span>
                    <span style="font-size: 0.82rem; font-weight: 700; color: #fbbf24;">{{ number_format($data['total_cash'], 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="card-body" style="padding: 0;">
                @if($data['items']->count() > 0)
                <table class="table" style="margin: 0; width: 100%;">
                    <thead>
                        <tr>
                            <th style="color: #94a3b8;">Pecahan</th>
                            <th style="text-align: center; color: #94a3b8;">Jml</th>
                            <th style="text-align: right; color: #94a3b8;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['items'] as $item)
                        <tr>
                            <td style="color: #f1f5f9;">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                            <td style="text-align: center; color: #fbbf24; font-weight: 600;">{{ $item->total }}</td>
                            <td style="text-align: right; color: #f1f5f9; font-weight: 600;">Rp {{ number_format($item->nominal * $item->total, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: rgba(0,0,0,0.1);">
                            <td style="font-weight: 700; color: #f1f5f9;">Total Closing</td>
                            <td style="text-align: center; font-weight: 700; color: #fbbf24;">{{ $data['items']->sum('total') }}</td>
                            <td style="text-align: right; font-weight: 700; color: #34d399;">Rp {{ number_format($data['total_cash'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
                @else
                <div style="padding: 1.5rem; text-align: center; color: #64748b; font-size: 0.82rem;">
                    Belum input closing
                </div>
                @endif
            </div>
            {{-- Selisih --}}
            @php $selisih = $data['total_modal'] - $data['total_cash']; @endphp
            <div style="padding: 0.6rem 1.25rem; border-top: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.75rem; color: #94a3b8;">Selisih (Modal - Closing)</span>
                <span style="font-weight: 700; color: {{ $selisih == 0 ? '#34d399' : ($selisih > 0 ? '#fbbf24' : '#ef4444') }};">
                    {{ $selisih >= 0 ? '+' : '' }}Rp {{ number_format($selisih, 0, ',', '.') }}
                </span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
    .closing-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
    @media (max-width: 1024px) { .closing-grid { grid-template-columns: 1fr; } }
    @media (max-width: 768px) {
        .content > div[style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$('#cashForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', background: '#1e293b', color: '#f1f5f9', confirmButtonColor: '#3b82f6' }).then(function() { window.location.reload(); });
        },
        error: function(xhr) {
            var errors = xhr.responseJSON?.errors || {};
            var msg = Object.values(errors).flat().join('<br>') || 'Terjadi kesalahan';
            Swal.fire({ title: 'Gagal', html: msg, icon: 'error', background: '#1e293b', color: '#f1f5f9' });
        }
    });
});

function deleteCash(id) {
    Swal.fire({
        title: 'Hapus data ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="bi bi-trash3"></i> Hapus',
        cancelButtonText: 'Batal',
        background: '#1e293b',
        color: '#f1f5f9',
    }).then(function(result) {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ url("uang-cash") }}/' + id,
                type: 'DELETE',
                success: function(res) {
                    Swal.fire({ title: 'Terhapus!', text: res.message, icon: 'success', background: '#1e293b', color: '#f1f5f9', confirmButtonColor: '#3b82f6' }).then(function() { window.location.reload(); });
                }
            });
        }
    });
}
</script>
@endpush
