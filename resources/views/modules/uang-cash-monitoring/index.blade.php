@extends('layouts.app')

@php
    use App\Models\Transaksi\UangCashMonitoring;
    use Carbon\Carbon;

    $selectedDate = request('tanggal', Carbon::today()->format('Y-m-d'));

    // Group by user untuk tanggal terpilih
    $dataPerUser = UangCashMonitoring::with('user')
        ->whereDate('created_at', $selectedDate)
        ->orderBy('user_id')
        ->orderBy('nominal', 'desc')
        ->get()
        ->groupBy('user_id');

    $grandTotal = $dataPerUser->flatten()->sum(fn($i) => $i->nominal * $i->total);
@endphp

@section('content')

{{-- Header --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h5><i class="bi bi-eye" style="color: var(--primary); margin-right: 0.5rem;"></i> Uang Cash Monitoring — Modal Harian</h5>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="bi bi-plus-lg"></i> Tambah Data
        </button>
    </div>
    <div class="card-body" style="padding: 1rem 1.5rem;">
        <div style="display: flex; align-items: flex-end; gap: 1.5rem; flex-wrap: wrap;">
            {{-- Date Picker --}}
            <div>
                <label style="font-size: 0.72rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem; display: block;">Pilih Tanggal</label>
                <div class="date-input-wrap">
                    <i class="bi bi-calendar3"></i>
                    <input type="text" id="filterDate" class="date-input" value="{{ $selectedDate }}" readonly>
                </div>
            </div>
            {{-- Grand Total --}}
            <div style="margin-left: auto; text-align: right;">
                <div style="font-size: 0.72rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Total Modal {{ Carbon::parse($selectedDate)->translatedFormat('d M Y') }}</div>
                <div style="font-size: 1.4rem; font-weight: 800; color: #34d399;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Cards per User --}}
<div class="ruko-grid">
    @forelse($dataPerUser as $userId => $items)
    @php $user = $items->first()->user; $totalModal = $items->sum(fn($i) => $i->nominal * $i->total); @endphp
    <div class="card">
        <div class="card-header">
            <h5 style="font-size: 0.9rem;">
                <i class="bi bi-person-circle" style="color: #60a5fa; margin-right: 0.4rem;"></i> {{ $user?->username ?? 'Unknown' }}
            </h5>
            <span style="font-size: 0.82rem; font-weight: 700; color: #34d399;">Rp {{ number_format($totalModal, 0, ',', '.') }}</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="table" style="margin: 0; width: 100%;">
                <thead>
                    <tr>
                        <th style="color: #94a3b8;">Pecahan</th>
                        <th style="text-align: center; color: #94a3b8;">Jml</th>
                        <th style="text-align: right; color: #94a3b8;">Subtotal</th>
                        <th style="text-align: center; color: #94a3b8; width: 90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td style="color: #f1f5f9; font-weight: 500;">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                        <td style="text-align: center; color: #fbbf24; font-weight: 600;">{{ $item->total }}</td>
                        <td style="text-align: right; color: #f1f5f9; font-weight: 600;">Rp {{ number_format($item->nominal * $item->total, 0, ',', '.') }}</td>
                        <td style="text-align: center; white-space: nowrap;">
                            <div style="display: inline-flex; gap: 4px;">
                                <button type="button" class="btn btn-sm btn-info" onclick="editItem({{ $item->id }}, {{ $item->nominal }}, {{ $item->total }})" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteItem({{ $item->id }})" title="Hapus">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: rgba(0,0,0,0.1);">
                        <td style="font-weight: 700; color: #f1f5f9;">Total</td>
                        <td style="text-align: center; font-weight: 700; color: #fbbf24;">{{ $items->sum('total') }}</td>
                        <td style="text-align: right; font-weight: 700; color: #34d399;">Rp {{ number_format($totalModal, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @empty
    <div class="card" style="grid-column: 1 / -1;">
        <div class="card-body" style="text-align: center; padding: 3rem; color: #64748b;">
            <i class="bi bi-inbox" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
            Tidak ada data monitoring untuk tanggal ini
        </div>
    </div>
    @endforelse
</div>

{{-- Modal Tambah --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Monitoring Cash</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="monitorForm" method="POST" action="{{ url('uang-cash-monitoring') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">Penanggung Jawab <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach(\App\Models\User::where('status', 1)->orderBy('username')->get() as $u)
                                <option value="{{ $u->id }}">{{ $u->username }}</option>
                            @endforeach
                        </select>
                    </div>
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
                            <input type="number" name="total" class="form-control" placeholder="Jumlah" required min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">Cabang / Ruko</label>
                        <select name="cabang_toko" class="form-select">
                            <option value="1">Ruko 1</option>
                            <option value="2">Ruko 2</option>
                            <option value="3">Ruko 3</option>
                            <option value="4">Ruko 4</option>
                            <option value="5">Ruko 5</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan..."></textarea>
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
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Jumlah</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">Pecahan</label>
                        <input type="text" id="edit_nominal_display" class="form-control" disabled style="background: rgba(255,255,255,0.06) !important; color: #f1f5f9 !important; -webkit-text-fill-color: #f1f5f9 !important; border-color: rgba(255,255,255,0.1) !important; opacity: 1 !important;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">Jumlah Lembar <span class="text-danger">*</span></label>
                        <input type="number" name="total" id="edit_total" class="form-control" required min="0" autofocus>
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
    .ruko-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
    @media (max-width: 1024px) { .ruko-grid { grid-template-columns: 1fr; } }

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
        min-width: 170px;
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
    .flatpickr-day.prevMonthDay, .flatpickr-day.nextMonthDay { color: #475569 !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
var addModal = new bootstrap.Modal(document.getElementById('addModal'));
var editModal = new bootstrap.Modal(document.getElementById('editModal'));

// Calendar picker - navigate on date change
flatpickr('#filterDate', {
    dateFormat: 'Y-m-d',
    disableMobile: true,
    onChange: function(selectedDates, dateStr) {
        window.location.href = '{{ url("uang-cash-monitoring") }}?tanggal=' + dateStr;
    }
});

function openAddModal() { addModal.show(); }

function editItem(id, nominal, total) {
    $('#edit_id').val(id);
    $('#edit_nominal_display').val('Rp ' + Number(nominal).toLocaleString('id'));
    $('#edit_total').val(total);
    editModal.show();
}

function deleteItem(id) {
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
                url: '{{ url("uang-cash-monitoring") }}/' + id,
                type: 'DELETE',
                success: function(res) {
                    Swal.fire({ title: 'Terhapus!', text: res.message, icon: 'success', background: '#1e293b', color: '#f1f5f9', confirmButtonColor: '#3b82f6' }).then(function() { window.location.reload(); });
                },
                error: function() {
                    Swal.fire({ title: 'Gagal', text: 'Tidak bisa menghapus data', icon: 'error', background: '#1e293b', color: '#f1f5f9' });
                }
            });
        }
    });
}

$('#monitorForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: $(this).attr('action'),
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
    var id = $('#edit_id').val();
    $.ajax({
        url: '{{ url("uang-cash-monitoring") }}/' + id,
        type: 'PUT',
        data: { _token: $('meta[name="csrf-token"]').attr('content'), total: $('#edit_total').val() },
        success: function(res) {
            editModal.hide();
            Swal.fire({ title: 'Berhasil!', text: res.message || 'Data diupdate', icon: 'success', background: '#1e293b', color: '#f1f5f9', confirmButtonColor: '#3b82f6' }).then(function() { window.location.reload(); });
        },
        error: function(xhr) {
            Swal.fire({ title: 'Gagal', text: 'Tidak bisa update data', icon: 'error', background: '#1e293b', color: '#f1f5f9' });
        }
    });
});
</script>
@endpush
