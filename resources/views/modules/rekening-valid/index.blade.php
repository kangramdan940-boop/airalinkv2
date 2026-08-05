@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h5><i class="bi bi-credit-card" style="color: var(--primary); margin-right: 0.5rem;"></i> Rekening Valid</h5>
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i class="bi bi-plus-lg"></i> Tambah Rekening
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

<!-- Modal -->
<div class="modal fade" id="formModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Rekening</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="rekForm" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="f_id">
                    <div class="mb-3">
                        <label class="form-label" style="color:#cbd5e1;">No Rekening <span class="text-danger">*</span></label>
                        <input type="text" name="no_rekening" id="f_no_rekening" class="form-control" placeholder="Nomor rekening" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:#cbd5e1;">Atas Nama <span class="text-danger">*</span></label>
                        <input type="text" name="atas_nama" id="f_atas_nama" class="form-control" placeholder="Nama pemilik" required style="text-transform:uppercase;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:#cbd5e1;">Bank <span class="text-danger">*</span></label>
                        <select name="kode_bank" id="f_kode_bank" class="form-select" required>
                            <option value="">-- Pilih Bank --</option>
                            @foreach(config('banks') as $bank)
                                <option value="{{ $bank['code'] }}-{{ $bank['name'] }}">{{ $bank['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="nama_bank" id="f_nama_bank">
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
            emptyTable: 'Tidak ada data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            search: '',
            searchPlaceholder: 'Cari rekening...',
            paginate: { previous: '‹', next: '›' }
        },
        dom: '<"dt-header"lf>rt<"dt-footer"ip>',
    });

    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        $.ajax({
            url: pageUrl + '/' + id + '/edit',
            type: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(res) {
                var r = res.record;
                $('#modalTitle').text('Edit Rekening');
                $('#btnSubmit').html('<i class="bi bi-check-lg"></i> Update');
                $('#f_id').val(r.id);
                $('#f_no_rekening').val(r.no_rekening);
                $('#f_atas_nama').val(r.atas_nama);
                $('#f_kode_bank').val(r.kode_bank).trigger('change');
                modal.show();
            }
        });
    });

    $('#rekForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#f_id').val();
        var method = id ? 'PUT' : 'POST';
        var url = id ? pageUrl + '/' + id : pageUrl;

        // Set nama_bank from select text
        var bankText = $('#f_kode_bank option:selected').text();
        $('#f_nama_bank').val(bankText !== '-- Pilih Bank --' ? bankText : '');

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize() + '&_method=' + method,
            success: function(res) {
                modal.hide();
                Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', background: '#1e293b', color: '#f1f5f9', confirmButtonColor: '#3b82f6' });
                dataTable.ajax.reload();
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors || {};
                var msg = Object.values(errors).flat().join('<br>') || 'Terjadi kesalahan';
                Swal.fire({ title: 'Gagal', html: msg, icon: 'error', background: '#1e293b', color: '#f1f5f9' });
            }
        });
    });
});

function openCreateModal() {
    $('#modalTitle').text('Tambah Rekening');
    $('#btnSubmit').html('<i class="bi bi-check-lg"></i> Simpan');
    $('#f_id').val('');
    $('#f_no_rekening').val('');
    $('#f_atas_nama').val('');
    $('#f_kode_bank').val('').trigger('change');
    modal.show();
}
</script>
@endpush

@push('styles')
<style>
    .dt-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem; gap: 1rem; }
    .dt-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 1.2rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.04); }
</style>
@endpush
