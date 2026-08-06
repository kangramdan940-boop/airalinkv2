@extends('layouts.app')

@section('content')

{{-- Header --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h5><i class="bi bi-graph-up-arrow" style="color: var(--primary); margin-right: 0.5rem;"></i> Investor & Bagi Hasil</h5>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="bi bi-plus-lg"></i> Tambah Investor
        </button>
    </div>
    <div class="card-body" style="padding: 1rem 1.5rem;">
        <div style="display: flex; align-items: flex-end; gap: 1.5rem; flex-wrap: wrap;">
            <div>
                <label style="font-size: 0.72rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem; display: block;">Periode</label>
                <div class="date-input-wrap">
                    <i class="bi bi-calendar3"></i>
                    <input type="text" id="filterBulan" class="date-input" value="{{ \Carbon\Carbon::parse($bulan . '-01')->translatedFormat('F Y') }}" readonly>
                </div>
            </div>
            <div style="margin-left: auto; text-align: right;">
                <div style="font-size: 0.72rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Total Keuntungan Bulan Ini</div>
                <div style="font-size: 1.4rem; font-weight: 800; color: #34d399;">Rp {{ number_format($totalKeuntungan, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Investor Cards --}}
<div class="investor-grid">
    @forelse($investors as $inv)
    @php
        $bagiHasil = $inv->keuntungan_user > 0 ? $inv->keuntungan_bersih * ($inv->persentase / 100) : 0;
    @endphp
    <div class="card {{ !$inv->status ? 'opacity-50' : '' }}">
        <div class="card-header">
            <h5 style="font-size: 0.9rem;">
                <i class="bi bi-person-circle" style="color: {{ $inv->status ? '#60a5fa' : '#64748b' }}; margin-right: 0.4rem;"></i>
                {{ $inv->user?->username ?? '-' }}
                @if(!$inv->status)
                <span class="badge bg-secondary" style="font-size: 0.65rem; margin-left: 0.3rem;">Nonaktif</span>
                @endif
            </h5>
            <span style="font-size: 1.2rem; font-weight: 800; color: #a5b4fc;">{{ $inv->persentase }}%</span>
        </div>
        <div class="card-body" style="padding: 1.2rem 1.5rem;">
            @if($inv->keuntungan_user > 0)
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem;">
                <div>
                    <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Keuntungan</div>
                    <div style="font-size: 1rem; font-weight: 700; color: #f1f5f9;">Rp {{ number_format($inv->keuntungan_user, 0, ',', '.') }}</div>
                </div>
                <div>
                    <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Pengeluaran</div>
                    <div style="font-size: 1rem; font-weight: 700; color: #ef4444;">Rp {{ number_format($inv->pengeluaran_user, 0, ',', '.') }}</div>
                </div>
                <div>
                    <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Keuntungan Bersih</div>
                    <div style="font-size: 1rem; font-weight: 700; color: {{ $inv->keuntungan_bersih >= 0 ? '#34d399' : '#ef4444' }};">Rp {{ number_format($inv->keuntungan_bersih, 0, ',', '.') }}</div>
                </div>
                <div>
                    <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Bagi Hasil ({{ $inv->persentase }}%)</div>
                    <div style="font-size: 1rem; font-weight: 700; color: #a5b4fc;">Rp {{ number_format($bagiHasil, 0, ',', '.') }}</div>
                </div>
            </div>
            @else
            <div style="text-align: center; color: #64748b; padding: 0.5rem 0;">
                <span style="font-size: 1.2rem;">-</span>
                <div style="font-size: 0.75rem; margin-top: 0.3rem;">Belum ada keuntungan bulan ini</div>
            </div>
            @endif
            @if($inv->keterangan)
            <div style="margin-top: 0.8rem; padding-top: 0.8rem; border-top: 1px solid rgba(255,255,255,0.06); font-size: 0.8rem; color: #64748b;">
                {{ $inv->keterangan }}
            </div>
            @endif
        </div>
        <div style="padding: 0.6rem 1.5rem; border-top: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: flex-end; gap: 0.3rem;">
            <button type="button" class="btn btn-sm btn-info" onclick="editInvestor({{ $inv->id }}, {{ $inv->persentase }}, '{{ $inv->keterangan }}', {{ $inv->status ? 1 : 0 }})" title="Edit">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-sm btn-danger" onclick="deleteInvestor({{ $inv->id }})" title="Hapus">
                <i class="bi bi-trash3"></i>
            </button>
        </div>
    </div>
    @empty
    <div class="card" style="grid-column: 1 / -1;">
        <div class="card-body" style="text-align: center; padding: 3rem; color: #64748b;">
            <i class="bi bi-people" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
            Belum ada investor. Klik "Tambah Investor" untuk menambahkan.
        </div>
    </div>
    @endforelse
</div>

{{-- Ringkasan Bagi Hasil --}}
@if($investors->where('status', 1)->count() > 0)
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h5 style="font-size: 0.9rem;"><i class="bi bi-pie-chart" style="color: #fbbf24; margin-right: 0.4rem;"></i> Ringkasan Bagi Hasil</h5>
    </div>
    <div class="card-body" style="padding: 0; overflow-x: auto;">
        <table class="table" style="margin: 0; width: 100%;">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">#</th>
                    <th style="width: 15%;">Investor</th>
                    <th style="width: 15%; text-align: right;">Keuntungan</th>
                    <th style="width: 15%; text-align: right;">Pengeluaran</th>
                    <th style="width: 15%; text-align: right;">Bersih</th>
                    <th style="width: 8%; text-align: center;">%</th>
                    <th style="width: 15%; text-align: right;">Bagi Hasil</th>
                    <th style="width: 12%;">Ket</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; $totalBagi = 0; @endphp
                @foreach($investors->where('status', 1) as $inv)
                @php
                    $bagi = $inv->keuntungan_user > 0 ? $inv->keuntungan_bersih * ($inv->persentase / 100) : 0;
                    $totalBagi += $bagi;
                @endphp
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $no++ }}</td>
                    <td style="color: #f1f5f9; font-weight: 600;">{{ $inv->user?->username ?? '-' }}</td>
                    <td style="text-align: right; color: #f1f5f9;">{{ $inv->keuntungan_user > 0 ? 'Rp ' . number_format($inv->keuntungan_user, 0, ',', '.') : '-' }}</td>
                    <td style="text-align: right; color: #ef4444;">{{ $inv->keuntungan_user > 0 ? 'Rp ' . number_format($inv->pengeluaran_user, 0, ',', '.') : '-' }}</td>
                    <td style="text-align: right; color: {{ $inv->keuntungan_bersih >= 0 ? '#34d399' : '#ef4444' }}; font-weight: 700;">{{ $inv->keuntungan_user > 0 ? 'Rp ' . number_format($inv->keuntungan_bersih, 0, ',', '.') : '-' }}</td>
                    <td style="text-align: center; color: #a5b4fc; font-weight: 700;">{{ $inv->persentase }}%</td>
                    <td style="text-align: right; color: #34d399; font-weight: 700;">{{ $inv->keuntungan_user > 0 ? 'Rp ' . number_format($bagi, 0, ',', '.') : '-' }}</td>
                    <td style="color: #94a3b8; font-size: 0.78rem;">{{ $inv->keterangan ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: rgba(0,0,0,0.1); border-top: 2px solid var(--border);">
                    <td colspan="2" style="font-weight: 700; color: #f1f5f9;">TOTAL</td>
                    <td style="text-align: right; font-weight: 700; color: #f1f5f9;">Rp {{ number_format($totalKeuntungan, 0, ',', '.') }}</td>
                    <td colspan="3"></td>
                    <td style="text-align: right; font-weight: 800; color: #34d399; font-size: 1.05rem;">Rp {{ number_format($totalBagi, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif

{{-- Modal Tambah --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Investor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">User <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- Pilih User --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->username }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">Persentase (%) <span class="text-danger">*</span></label>
                        <input type="number" name="persentase" class="form-control" placeholder="50" required min="0" max="100" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">Keterangan</label>
                        <input type="text" name="keterangan" class="form-control" placeholder="Opsional">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                        <i class="bi bi-check-lg"></i> Simpan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Investor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">Persentase (%) <span class="text-danger">*</span></label>
                        <input type="number" name="persentase" id="edit_persentase" class="form-control" required min="0" max="100" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">Keterangan</label>
                        <input type="text" name="keterangan" id="edit_keterangan" class="form-control" placeholder="Opsional">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">Status</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                        <i class="bi bi-check-lg"></i> Update
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .investor-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
    }
    @media (max-width: 1200px) { .investor-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 768px) { .investor-grid { grid-template-columns: 1fr; } }
    .opacity-50 { opacity: 0.5; }

    .date-input-wrap { position: relative; display: flex; align-items: center; }
    .date-input-wrap i { position: absolute; left: 12px; color: #60a5fa; font-size: 0.9rem; pointer-events: none; z-index: 1; }
    .date-input {
        padding: 0.6rem 0.9rem 0.6rem 2.4rem;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        color: #f1f5f9;
        font-size: 0.85rem;
        font-family: 'Inter', sans-serif;
        min-width: 180px;
        height: 42px;
        cursor: pointer;
        transition: all 0.25s ease;
    }
    .date-input:focus { outline: none; border-color: rgba(59,130,246,0.5); box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }

    /* Flatpickr Dark */
    .flatpickr-calendar { background: rgba(30,41,59,0.95) !important; backdrop-filter: blur(20px) !important; border: 1px solid rgba(255,255,255,0.1) !important; border-radius: 16px !important; box-shadow: 0 20px 50px rgba(0,0,0,0.5) !important; font-family: 'Inter', sans-serif !important; }
    .flatpickr-months .flatpickr-month { background: rgba(0,0,0,0.2) !important; color: #f1f5f9 !important; }
    .flatpickr-current-month .flatpickr-monthDropdown-months { background: transparent !important; color: #f1f5f9 !important; }
    .flatpickr-current-month input.cur-year { color: #f1f5f9 !important; }
    .flatpickr-months .flatpickr-prev-month svg, .flatpickr-months .flatpickr-next-month svg { fill: #f1f5f9 !important; }
    span.flatpickr-weekday { color: #64748b !important; font-weight: 600 !important; }
    .flatpickr-day { color: #cbd5e1 !important; border-radius: 8px !important; border: none !important; }
    .flatpickr-day:hover { background: rgba(59,130,246,0.15) !important; }
    .flatpickr-day.selected { background: #3b82f6 !important; color: #fff !important; box-shadow: 0 4px 12px rgba(59,130,246,0.3) !important; }
    .flatpickr-day.today { color: #60a5fa !important; font-weight: 700 !important; }
    .flatpickr-day.flatpickr-disabled { color: #334155 !important; }
    .flatpickr-monthSelect-month { color: #cbd5e1 !important; background: transparent !important; border-radius: 8px !important; }
    .flatpickr-monthSelect-month:hover { background: rgba(59,130,246,0.15) !important; }
    .flatpickr-monthSelect-month.selected { background: #3b82f6 !important; color: #fff !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
<script>
var addModal = new bootstrap.Modal(document.getElementById('addModal'));
var editModal = new bootstrap.Modal(document.getElementById('editModal'));

flatpickr('#filterBulan', {
    plugins: [new monthSelectPlugin({ shorthand: false, dateFormat: "Y-m", altFormat: "F Y" })],
    defaultDate: '{{ $bulan }}',
    disableMobile: true,
    onChange: function(selectedDates, dateStr) {
        window.location.href = '{{ url("investor") }}?bulan=' + dateStr;
    }
});

function openAddModal() { addModal.show(); }

function editInvestor(id, persentase, keterangan, status) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_persentase').value = persentase;
    document.getElementById('edit_keterangan').value = keterangan || '';
    document.getElementById('edit_status').value = status;
    editModal.show();
}

function deleteInvestor(id) {
    Swal.fire({
        title: 'Hapus investor ini?',
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
                url: '{{ url("investor") }}/' + id,
                type: 'DELETE',
                success: function(res) {
                    Swal.fire({ title: 'Terhapus!', text: res.message, icon: 'success', background: '#1e293b', color: '#f1f5f9', confirmButtonColor: '#3b82f6' }).then(function() { window.location.reload(); });
                }
            });
        }
    });
}

$('#addForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: '{{ url("investor") }}',
        type: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            addModal.hide();
            Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', background: '#1e293b', color: '#f1f5f9', confirmButtonColor: '#3b82f6' }).then(function() { window.location.reload(); });
        },
        error: function(xhr) {
            var errors = xhr.responseJSON?.errors || {};
            var msg = Object.values(errors).flat().join('<br>') || 'Terjadi kesalahan';
            Swal.fire({ title: 'Gagal', html: msg, icon: 'error', background: '#1e293b', color: '#f1f5f9' });
        }
    });
});

$('#editForm').on('submit', function(e) {
    e.preventDefault();
    var id = document.getElementById('edit_id').value;
    $.ajax({
        url: '{{ url("investor") }}/' + id,
        type: 'PUT',
        data: $(this).serialize(),
        success: function(res) {
            editModal.hide();
            Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', background: '#1e293b', color: '#f1f5f9', confirmButtonColor: '#3b82f6' }).then(function() { window.location.reload(); });
        },
        error: function(xhr) {
            var errors = xhr.responseJSON?.errors || {};
            var msg = Object.values(errors).flat().join('<br>') || 'Terjadi kesalahan';
            Swal.fire({ title: 'Gagal', html: msg, icon: 'error', background: '#1e293b', color: '#f1f5f9' });
        }
    });
});
</script>
@endpush
