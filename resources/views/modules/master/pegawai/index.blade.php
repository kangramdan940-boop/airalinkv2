@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h5><i class="bi bi-people-fill" style="color: var(--primary); margin-right: 0.5rem;"></i> Data Pegawai</h5>
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i class="bi bi-plus-lg"></i> Tambah Pegawai
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table" id="dataTable" style="width:100%">
                <thead>
                    <tr>
                        @foreach($tableStruct as $col)
                            <th class="{{ $col['className'] ?? '' }}" style="width:{{ $col['width'] ?? 'auto' }}">{{ $col['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal Create/Edit -->
<div class="modal fade" id="formModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Pegawai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="pegawaiForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="id" id="formId">

                    <div class="mb-3">
                        <label class="form-label">Nama Pegawai <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="f_nama" class="form-control" placeholder="Masukkan nama pegawai" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="telepon" id="f_telepon" class="form-control" placeholder="08xxxxxxxxxx" maxlength="15">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="f_status" class="form-select">
                            <option value="0">Probation</option>
                            <option value="1">Aktif</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" id="f_keterangan" class="form-control" rows="3" placeholder="Catatan tambahan..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;" id="btnSubmit">
                        <i class="bi bi-check-lg"></i> Simpan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var dataTable;
var pageUrl = '{{ url($pageUrl) }}';
var modal = new bootstrap.Modal(document.getElementById('formModal'));

$(function() {
    dataTable = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: pageUrl + '/grid', type: 'POST' },
        columns: @json($tableStruct),
        order: [],
        pageLength: 25,
        language: {
            processing: '<div style="color: var(--primary)"><i class="bi bi-arrow-repeat"></i> Memuat...</div>',
            emptyTable: 'Tidak ada data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            lengthMenu: 'Tampilkan _MENU_',
            search: '',
            searchPlaceholder: 'Cari...',
            paginate: { previous: '‹', next: '›' }
        },
        dom: '<"dt-header"lf>rt<"dt-footer"ip>',
    });

    // Edit button click
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        openEditModal(id);
    });

    // Form submit
    $('#pegawaiForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#formId').val();
        var method = id ? 'PUT' : 'POST';
        var url = id ? pageUrl + '/' + id : pageUrl;

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize() + '&_method=' + method,
            success: function(res) {
                modal.hide();
                Swal.fire({ title: 'Berhasil!', text: res.message || 'Data berhasil disimpan.', icon: 'success', background: '#1e293b', color: '#f1f5f9', confirmButtonColor: '#3b82f6' });
                dataTable.ajax.reload();
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors || {};
                var msg = Object.values(errors).flat().join('<br>') || 'Terjadi kesalahan';
                Swal.fire({ title: 'Validasi Gagal', html: msg, icon: 'error', background: '#1e293b', color: '#f1f5f9' });
            }
        });
    });
});

function openCreateModal() {
    $('#modalTitle').text('Tambah Pegawai');
    $('#btnSubmit').html('<i class="bi bi-check-lg"></i> Simpan');
    $('#formId').val('');
    $('#formMethod').val('POST');
    $('#f_nama').val('');
    $('#f_telepon').val('');
    $('#f_status').val('0');
    $('#f_keterangan').val('');
    modal.show();
}

function openEditModal(id) {
    $.ajax({
        url: pageUrl + '/' + id + '/edit',
        type: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(res) {
            var r = res.record;
            $('#modalTitle').text('Edit Pegawai');
            $('#btnSubmit').html('<i class="bi bi-check-lg"></i> Update');
            $('#formId').val(r.id);
            $('#formMethod').val('PUT');
            $('#f_nama').val(r.nama || '');
            $('#f_telepon').val(r.telepon || '');
            $('#f_status').val(r.status != null ? r.status : '0');
            $('#f_keterangan').val(r.keterangan || '');
            modal.show();
        }
    });
}
</script>
@endpush

@push('styles')
<style>
    .dt-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem; gap: 1rem; }
    .dt-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 1.2rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.04); }
    .dataTables_wrapper .dataTables_length { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: var(--text-muted); }
    .dataTables_wrapper .dataTables_filter { display: flex; align-items: center; }
    .dataTables_wrapper .dataTables_filter label { display: flex; align-items: center; gap: 0.5rem; }
</style>
@endpush
