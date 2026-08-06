@extends('layouts.app')

@section('content')

{{-- Header --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h5><i class="bi bi-cash-coin" style="color: var(--primary); margin-right: 0.5rem;"></i> Pengeluaran Bulanan</h5>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="bi bi-plus-lg"></i> Tambah Item
        </button>
    </div>
    <div class="card-body" style="padding: 1rem 1.5rem;">
        <div style="display: flex; align-items: flex-end; gap: 1.5rem; flex-wrap: wrap;">
            <div>
                <label style="font-size: 0.72rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem; display: block;">Bulan</label>
                <div class="date-input-wrap">
                    <i class="bi bi-calendar3"></i>
                    <input type="text" id="filterBulan" class="date-input" value="{{ \Carbon\Carbon::parse($bulan . '-01')->translatedFormat('F Y') }}" readonly>
                </div>
            </div>
            <div>
                <label style="font-size: 0.72rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem; display: block;">Karyawan</label>
                <select id="filterUser" class="form-select" style="max-width: 200px;">
                    <option value="">-- Semua --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>{{ $u->username }}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-left: auto; text-align: right;">
                <div style="font-size: 0.72rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Total Pengeluaran / Bulan</div>
                <div style="font-size: 1.4rem; font-weight: 800; color: #ef4444;">Rp {{ number_format($totalBulanan, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Content per User --}}
@if($pengeluaranPerUser->count() > 0)
    @foreach($pengeluaranPerUser as $uid => $items)
    @php
        $itemUser = $items->first()->user;
        $userTotal = $items->sum(fn($i) => $i->nominal_bulanan);
    @endphp
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h5 style="font-size: 0.9rem;">
                <i class="bi bi-person-circle" style="color: #60a5fa; margin-right: 0.4rem;"></i>
                {{ $itemUser?->username ?? 'Umum' }}
            </h5>
            <span style="font-size: 0.9rem; font-weight: 700; color: #ef4444;">Rp {{ number_format($userTotal, 0, ',', '.') }}/bln</span>
        </div>
        <div class="card-body" style="padding: 0; overflow-x: auto;">
            <table class="table" style="margin: 0; width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">#</th>
                        <th style="width: 22%;">Nama</th>
                        <th style="width: 15%; text-align: right;">Nominal</th>
                        <th style="width: 10%; text-align: center;">Tipe</th>
                        <th style="width: 18%; text-align: right;">Per Bulan</th>
                        <th style="width: 18%;">Keterangan</th>
                        <th style="width: 12%; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $i => $item)
                    <tr>
                        <td style="text-align: center; color: #64748b;">{{ $i + 1 }}</td>
                        <td style="color: #f1f5f9; font-weight: 600;">{{ $item->nama }}</td>
                        <td style="text-align: right; color: #f1f5f9;">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                        <td style="text-align: center;">
                            <span class="badge {{ $item->tipe === 'harian' ? 'bg-info' : 'bg-primary' }}">
                                {{ $item->tipe === 'harian' ? 'Harian' : 'Bulanan' }}
                            </span>
                        </td>
                        <td style="text-align: right; color: #34d399; font-weight: 700;">Rp {{ number_format($item->nominal_bulanan, 0, ',', '.') }}</td>
                        <td style="color: #94a3b8; font-size: 0.78rem;">{{ $item->keterangan ?? '-' }}</td>
                        <td style="text-align: center; white-space: nowrap;">
                            @if(strtolower($item->nama) === 'biaya-admin')
                            <button type="button" class="btn btn-sm btn-warning" onclick="openHitungModal({{ $item->id }})" title="Hitung Biaya Admin">
                                <i class="bi bi-calculator"></i>
                            </button>
                            @endif
                            <button type="button" class="btn btn-sm btn-info" onclick="editItem({{ $item->id }}, '{{ $item->nama }}', {{ $item->nominal }}, '{{ $item->tipe }}', '{{ $item->keterangan }}')" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteItem({{ $item->id }})" title="Hapus">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
@else
<div class="card">
    <div class="card-body" style="text-align: center; padding: 3rem; color: #64748b;">
        <i class="bi bi-inbox" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
        Pilih karyawan untuk melihat/generate data pengeluaran
    </div>
</div>
@endif

{{-- Info --}}
<div style="margin-top: 1rem; padding: 0.8rem 1.2rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 10px;">
    <div style="font-size: 0.78rem; color: #64748b;">
        <i class="bi bi-info-circle" style="margin-right: 0.3rem;"></i>
        Item bertipe <strong>Harian</strong> dihitung × 30 hari untuk estimasi bulanan. Preset: Gaji Rp 2.000.000/bln, Makan Rp 15.000/hari, Listrik Rp 200.000/bln.
    </div>
</div>

{{-- Modal Tambah --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Pengeluaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addForm">
                    @csrf
                    <input type="hidden" name="bulan" value="{{ $bulan }}">
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">Karyawan <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>{{ $u->username }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Transport, WiFi, dll" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="mb-3">
                            <label class="form-label" style="color: #cbd5e1;">Nominal <span class="text-danger">*</span></label>
                            <input type="number" name="nominal" class="form-control" placeholder="0" required min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="color: #cbd5e1;">Tipe <span class="text-danger">*</span></label>
                            <select name="tipe" class="form-select" required>
                                <option value="bulanan">Bulanan</option>
                                <option value="harian">Harian</option>
                            </select>
                        </div>
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
                <h5 class="modal-title">Edit Pengeluaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">Nama</label>
                        <input type="text" id="edit_nama" class="form-control" disabled style="background: rgba(255,255,255,0.06) !important; color: #f1f5f9 !important; -webkit-text-fill-color: #f1f5f9 !important; opacity: 1 !important;">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="mb-3">
                            <label class="form-label" style="color: #cbd5e1;">Nominal <span class="text-danger">*</span></label>
                            <input type="number" name="nominal" id="edit_nominal" class="form-control" required min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="color: #cbd5e1;">Tipe</label>
                            <input type="text" id="edit_tipe" class="form-control" disabled style="background: rgba(255,255,255,0.06) !important; color: #f1f5f9 !important; -webkit-text-fill-color: #f1f5f9 !important; opacity: 1 !important;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">Keterangan</label>
                        <input type="text" name="keterangan" id="edit_keterangan" class="form-control" placeholder="Opsional">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                        <i class="bi bi-check-lg"></i> Update
                    </button>
                </form>
            </div>
        </div>
    </div>
{{-- Modal Hitung Biaya Admin - Step 1: Pilih Periode --}}
<div class="modal fade" id="hitungModal" tabindex="-1" style="z-index: 10050;">
    <div class="modal-dialog">
        <div class="modal-content" style="background: var(--bg-card); border: 1px solid rgba(255,255,255,0.1);">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calculator" style="margin-right: 0.4rem;"></i> Hitung Biaya Admin (BI Fast)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="color: #94a3b8; font-size: 0.82rem; margin-bottom: 1rem;">
                    Pilih periode untuk menghitung biaya admin transfer non-BCA (× Rp 2.500/trx).
                </p>
                <input type="hidden" id="hitung_id">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" id="hitung_start" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">Tanggal Akhir <span class="text-danger">*</span></label>
                        <input type="date" id="hitung_end" class="form-control" required>
                    </div>
                </div>
                <button type="button" class="btn btn-warning" style="width: 100%; justify-content: center;" onclick="loadBanks()">
                    <i class="bi bi-search"></i> Lihat Daftar Bank
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Bank List - Step 2: Exclude banks --}}
<div class="modal fade" id="bankListModal" tabindex="-1" style="z-index: 10050;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background: var(--bg-card); border: 1px solid rgba(255,255,255,0.1);">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-bank" style="margin-right: 0.4rem;"></i> Pilih Bank yang Dikecualikan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="color: #94a3b8; font-size: 0.82rem; margin-bottom: 0.5rem;">
                    Checklist bank yang <strong>TIDAK</strong> kena biaya BI Fast (tidak dihitung × Rp 2.500).
                </p>
                <div style="margin-bottom: 1rem; padding: 0.6rem 1rem; background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.2); border-radius: 8px;">
                    <span style="font-size: 0.82rem; color: #93c5fd;">Total transaksi non-BCA: <strong id="bankTotalTrx">0</strong> trx</span>
                </div>
                <div id="bankListContainer" style="max-height: 350px; overflow-y: auto;">
                    {{-- populated by JS --}}
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.06);">
                <div style="width: 100%; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 0.82rem; color: #94a3b8;">
                        Dihitung: <strong id="bankCalcTrx" style="color: #34d399;">0</strong> trx = <strong id="bankCalcTotal" style="color: #34d399;">Rp 0</strong>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="submitHitung()">
                        <i class="bi bi-check-lg"></i> Hitung & Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Loading Overlay --}}
<div id="loadingOverlay" hidden style="position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.85); z-index:9999; display:flex; align-items:center; justify-content:center; flex-direction:column;">
    <div style="text-align:center;">
        <div class="spinner-border text-primary" style="width:3rem; height:3rem;" role="status"></div>
        <div style="color:#f1f5f9; margin-top:1rem; font-weight:600;">Menghitung biaya admin...</div>
        <div style="color:#94a3b8; font-size:0.8rem; margin-top:0.3rem;">Memproses transaksi transfer non-BCA</div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
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
    .flatpickr-calendar { background: rgba(30,41,59,0.95) !important; backdrop-filter: blur(20px) !important; border: 1px solid rgba(255,255,255,0.1) !important; border-radius: 16px !important; box-shadow: 0 20px 50px rgba(0,0,0,0.5) !important; font-family: 'Inter', sans-serif !important; }
    .flatpickr-months .flatpickr-month { background: rgba(0,0,0,0.2) !important; color: #f1f5f9 !important; }
    .flatpickr-current-month .flatpickr-monthDropdown-months { background: transparent !important; color: #f1f5f9 !important; }
    .flatpickr-current-month input.cur-year { color: #f1f5f9 !important; }
    .flatpickr-months .flatpickr-prev-month svg, .flatpickr-months .flatpickr-next-month svg { fill: #f1f5f9 !important; }
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
console.log('=== Pengeluaran script loaded ===');
var addModalEl = document.getElementById('addModal');
var editModalEl = document.getElementById('editModal');
console.log('addModalEl found:', !!addModalEl);
console.log('editModalEl found:', !!editModalEl);
var addModal = addModalEl ? new bootstrap.Modal(addModalEl) : null;
var editModal = editModalEl ? new bootstrap.Modal(editModalEl) : null;
console.log('addModal created:', !!addModal);
console.log('editModal created:', !!editModal);

var currentBulan = '{{ $bulan }}';

flatpickr('#filterBulan', {
    plugins: [new monthSelectPlugin({ shorthand: false, dateFormat: "Y-m", altFormat: "F Y" })],
    defaultDate: '{{ $bulan }}',
    disableMobile: true,
    onChange: function(selectedDates, dateStr) {
        currentBulan = dateStr;
        applyFilter();
    }
});

function applyFilter() {
    var userId = document.getElementById('filterUser').value;
    var url = '{{ url("pengeluaran") }}?bulan=' + currentBulan;
    if (userId) url += '&user_id=' + userId;
    window.location.href = url;
}

document.getElementById('filterUser').addEventListener('change', applyFilter);

function openAddModal() { if (addModal) addModal.show(); }

function editItem(id, nama, nominal, tipe, keterangan) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_nominal').value = nominal;
    document.getElementById('edit_tipe').value = tipe === 'harian' ? 'Harian' : 'Bulanan';
    document.getElementById('edit_keterangan').value = keterangan || '';
    editModal.show();
}

function deleteItem(id) {
    Swal.fire({
        title: 'Hapus item ini?',
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
                url: '{{ url("pengeluaran") }}/' + id,
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
        url: '{{ url("pengeluaran") }}',
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
        url: '{{ url("pengeluaran") }}/' + id,
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

// Hitung Biaya Admin - move modals to body to avoid overflow clipping
document.body.appendChild(document.getElementById('hitungModal'));
document.body.appendChild(document.getElementById('bankListModal'));
document.body.appendChild(document.getElementById('loadingOverlay'));

var hitungEl = document.getElementById('hitungModal');
var bankEl = document.getElementById('bankListModal');
console.log('hitungEl found:', !!hitungEl);
console.log('bankEl found:', !!bankEl);
var hitungModal = hitungEl ? new bootstrap.Modal(hitungEl) : null;
var bankListModal = bankEl ? new bootstrap.Modal(bankEl) : null;
console.log('hitungModal created:', !!hitungModal);
var bankData = [];

function openHitungModal(id) {
    try {
        console.log('openHitungModal called with id:', id);
        var el = document.getElementById('hitung_id');
        console.log('hitung_id element:', el);
        el.value = id;
        console.log('set value done');
        var now = new Date();
        var year = now.getFullYear();
        var month = String(now.getMonth() + 1).padStart(2, '0');
        var lastDay = new Date(year, now.getMonth() + 1, 0).getDate();
        document.getElementById('hitung_start').value = year + '-' + month + '-01';
        document.getElementById('hitung_end').value = year + '-' + month + '-' + lastDay;
        console.log('dates set, hitungModal:', !!hitungModal);
        if (hitungModal) {
            hitungModal.show();
            console.log('show called');
        }
    } catch(e) {
        console.error('ERROR in openHitungModal:', e.message, e);
    }
}

function loadBanks() {
    var id = document.getElementById('hitung_id').value;
    var startDate = document.getElementById('hitung_start').value;
    var endDate = document.getElementById('hitung_end').value;

    if (!startDate || !endDate) {
        Swal.fire({ title: 'Error', text: 'Pilih tanggal mulai dan akhir', icon: 'error', background: '#1e293b', color: '#f1f5f9' });
        return;
    }

    hitungModal.hide();
    var overlay = document.getElementById('loadingOverlay');
    overlay.removeAttribute('hidden');

    $.ajax({
        url: '{{ url("pengeluaran/get-banks") }}',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            pengeluaran_id: id,
            start_date: startDate,
            end_date: endDate,
        },
        success: function(res) {
            overlay.setAttribute('hidden', '');
            bankData = res.banks;
            renderBankList(res.banks, res.total_trx);
            bankListModal.show();
        },
        error: function(xhr) {
            overlay.setAttribute('hidden', '');
            Swal.fire({ title: 'Gagal', text: 'Tidak bisa mengambil data bank', icon: 'error', background: '#1e293b', color: '#f1f5f9' });
        }
    });
}

function renderBankList(banks, totalTrx) {
    document.getElementById('bankTotalTrx').textContent = totalTrx;
    var container = document.getElementById('bankListContainer');
    var html = '<table class="table" style="margin:0; width:100%;"><thead><tr>' +
        '<th style="width:40px; text-align:center;"><input type="checkbox" id="excludeAll"></th>' +
        '<th>Bank</th>' +
        '<th style="text-align:right; width:100px;">Jumlah Trx</th>' +
        '</tr></thead><tbody>';

    banks.forEach(function(bank) {
        html += '<tr>' +
            '<td style="text-align:center;"><input type="checkbox" class="bank-exclude" value="' + bank.kode_bank + '" data-trx="' + bank.total_trx + '"></td>' +
            '<td style="color:#f1f5f9;">' + bank.kode_bank + '</td>' +
            '<td style="text-align:right; color:#fbbf24; font-weight:600;">' + bank.total_trx + '</td>' +
            '</tr>';
    });

    html += '</tbody></table>';
    container.innerHTML = html;

    // Update calculation
    updateCalcPreview();

    // Event listeners
    document.getElementById('excludeAll').addEventListener('change', function() {
        document.querySelectorAll('.bank-exclude').forEach(function(cb) { cb.checked = this.checked; }.bind(this));
        updateCalcPreview();
    });

    document.querySelectorAll('.bank-exclude').forEach(function(cb) {
        cb.addEventListener('change', updateCalcPreview);
    });
}

function updateCalcPreview() {
    var totalTrx = parseInt(document.getElementById('bankTotalTrx').textContent);
    var excludedTrx = 0;
    document.querySelectorAll('.bank-exclude:checked').forEach(function(cb) {
        excludedTrx += parseInt(cb.dataset.trx);
    });
    var calcTrx = totalTrx - excludedTrx;
    var calcTotal = calcTrx * 2500;

    document.getElementById('bankCalcTrx').textContent = calcTrx;
    document.getElementById('bankCalcTotal').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(calcTotal);
}

function submitHitung() {
    var id = document.getElementById('hitung_id').value;
    var startDate = document.getElementById('hitung_start').value;
    var endDate = document.getElementById('hitung_end').value;

    // Get excluded banks
    var excludedBanks = [];
    document.querySelectorAll('.bank-exclude:checked').forEach(function(cb) {
        excludedBanks.push(cb.value);
    });

    bankListModal.hide();
    var overlay = document.getElementById('loadingOverlay');
    overlay.removeAttribute('hidden');

    $.ajax({
        url: '{{ url("pengeluaran/hitung-admin") }}',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            pengeluaran_id: id,
            start_date: startDate,
            end_date: endDate,
            excluded_banks: excludedBanks,
        },
        success: function(res) {
            overlay.setAttribute('hidden', '');
            Swal.fire({
                title: 'Berhasil!',
                html: '<div style="text-align:left; font-size:0.9rem;">' +
                    '<p><strong>Transfer dihitung:</strong> ' + res.jumlah_transfer + ' transaksi</p>' +
                    '<p><strong>Biaya Admin:</strong> Rp ' + new Intl.NumberFormat('id-ID').format(res.biaya_admin) + '</p>' +
                    '<p style="color:#94a3b8; font-size:0.8rem;">' + res.keterangan + '</p></div>',
                icon: 'success',
                background: '#1e293b',
                color: '#f1f5f9',
                confirmButtonColor: '#3b82f6'
            }).then(function() { window.location.reload(); });
        },
        error: function(xhr) {
            overlay.setAttribute('hidden', '');
            var errors = xhr.responseJSON?.errors || {};
            var msg = Object.values(errors).flat().join('<br>') || 'Terjadi kesalahan';
            Swal.fire({ title: 'Gagal', html: msg, icon: 'error', background: '#1e293b', color: '#f1f5f9' });
        }
    });
}
</script>
@endpush
