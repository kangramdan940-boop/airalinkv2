@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>{{ $title ?? 'Data' }}</h5>
        <button class="btn btn-primary btn-add" data-bs-toggle="modal" data-bs-target="#formModal">
            <i class="bi bi-plus-lg"></i> Tambah Data
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
    <div class="modal-dialog modal-{{ $modalSize ?? 'md' }}">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $title ?? 'Form' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody">
                @yield('form-content')
            </div>
        </div>
    </div>
</div>

@yield('extra-content')
@endsection

@push('scripts')
<script>
var dataTable;
var pageUrl = '{{ url($pageUrl) }}';

$(function() {
    dataTable = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: pageUrl + '/grid', type: 'POST' },
        columns: @json($tableStruct),
        order: [],
        language: {
            processing: '<div style="color: var(--primary)"><i class="bi bi-arrow-repeat"></i> Memuat...</div>',
            emptyTable: 'Tidak ada data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            lengthMenu: 'Tampilkan _MENU_ data',
            search: '',
            searchPlaceholder: 'Cari...',
            paginate: { previous: '‹', next: '›' }
        },
        dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
    });

    // Edit button
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        $.get(pageUrl + '/' + id + '/edit', function(html) {
            var content = $(html).find('#modalBody').html();
            $('#modalBody').html(content || html);
            $('#formModal').modal('show');
        });
    });

    // Add button
    $('.btn-add').on('click', function() {
        $.get(pageUrl + '/create', function(html) {
            var content = $(html).find('#modalBody').html();
            if (content) $('#modalBody').html(content);
        });
    });
});

// Form submit
$(document).on('submit', '#formModal form', function(e) {
    e.preventDefault();
    var form = $(this);
    var formData = new FormData(this);

    $.ajax({
        url: form.attr('action'),
        type: form.attr('method') || 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            $('#formModal').modal('hide');
            Swal.fire({ title: 'Berhasil!', text: res.message || 'Data berhasil disimpan.', icon: 'success', background: '#1e293b', color: '#f1f5f9', confirmButtonColor: '#3b82f6' });
            dataTable.ajax.reload();
        },
        error: function(xhr) {
            var errors = xhr.responseJSON?.errors || {};
            var msg = Object.values(errors).flat().join('<br>') || xhr.responseJSON?.message || 'Terjadi kesalahan.';
            Swal.fire({ title: 'Validasi Gagal', html: msg, icon: 'error', background: '#1e293b', color: '#f1f5f9' });
        }
    });
});
</script>
@endpush
