@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h5><i class="bi bi-person-fill-gear" style="color: var(--primary); margin-right: 0.5rem;"></i> Manajemen Pengguna</h5>
        <button class="btn btn-primary" onclick="openCreateModal()">
            <i class="bi bi-plus-lg"></i> Tambah User
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
                <h5 class="modal-title" id="modalTitle">Tambah User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="userForm" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="f_id">

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" id="f_username" class="form-control" placeholder="Username login" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="f_email" class="form-control" placeholder="email@domain.com" required>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="mb-3">
                            <label class="form-label">Password <span id="passRequired" class="text-danger">*</span></label>
                            <input type="password" name="password" id="f_password" class="form-control" placeholder="Min. 6 karakter" minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="confirm_password" id="f_confirm" class="form-control" placeholder="Ulangi password" minlength="6">
                        </div>
                    </div>
                    <div class="mb-3" id="passLamaWrap" style="display:none;">
                        <label class="form-label">Password Lama</label>
                        <input type="password" name="password_lama" id="f_password_lama" class="form-control" placeholder="Isi jika ingin ganti password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="roles[]" id="f_roles" class="form-select" required>
                            <option value="">-- Pilih Role --</option>
                            @foreach(\Spatie\Permission\Models\Role::all() as $role)
                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Limit Harian</label>
                        <input type="text" name="limit" id="f_limit" class="form-control" placeholder="Contoh: 50000000">
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

    // Edit button
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        openEditModal(id);
    });

    // Form submit
    $('#userForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#f_id').val();
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
                var msg = Object.values(errors).flat().join('<br>') || xhr.responseJSON?.message || 'Terjadi kesalahan';
                Swal.fire({ title: 'Validasi Gagal', html: msg, icon: 'error', background: '#1e293b', color: '#f1f5f9' });
            }
        });
    });
});

function openCreateModal() {
    $('#modalTitle').text('Tambah User');
    $('#btnSubmit').html('<i class="bi bi-person-plus"></i> Buat User');
    $('#f_id').val('');
    $('#f_username').val('');
    $('#f_email').val('');
    $('#f_password').val('').attr('required', true);
    $('#f_confirm').val('').attr('required', true);
    $('#f_password_lama').val('');
    $('#f_roles').val('');
    $('#f_limit').val('');
    $('#passRequired').show();
    $('#passLamaWrap').hide();
    modal.show();
}

function openEditModal(id) {
    $.ajax({
        url: pageUrl + '/' + id + '/edit',
        type: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(res) {
            var r = res.record;
            $('#modalTitle').text('Edit User');
            $('#btnSubmit').html('<i class="bi bi-check-lg"></i> Update');
            $('#f_id').val(r.id);
            $('#f_username').val(r.username);
            $('#f_email').val(r.email);
            $('#f_password').val('').removeAttr('required');
            $('#f_confirm').val('').removeAttr('required');
            $('#f_password_lama').val('');
            $('#f_roles').val(r.roles && r.roles.length > 0 ? r.roles[0].name : '');
            $('#f_limit').val(res.limit ? res.limit.limit : '');
            $('#passRequired').hide();
            $('#passLamaWrap').show();
            modal.show();
        }
    });
}

function resetPassword(id) {
    Swal.fire({
        title: 'Reset Password',
        input: 'text',
        inputLabel: 'Masukkan password baru',
        inputPlaceholder: 'Password baru (min 6 karakter)',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-key"></i> Reset',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#64748b',
        background: '#1e293b',
        color: '#f1f5f9',
        inputValidator: function(value) {
            if (!value) return 'Password tidak boleh kosong';
            if (value.length < 6) return 'Password minimal 6 karakter';
        }
    }).then(function(result) {
        if (result.isConfirmed) {
            $.ajax({
                url: pageUrl + '/' + id + '/reset-password',
                type: 'PUT',
                data: { new_password: result.value },
                success: function(res) {
                    Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', background: '#1e293b', color: '#f1f5f9', confirmButtonColor: '#3b82f6' });
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Gagal reset password';
                    Swal.fire({ title: 'Gagal', text: msg, icon: 'error', background: '#1e293b', color: '#f1f5f9' });
                }
            });
        }
    });
}
</script>
@endpush

@push('styles')
<style>
    .dt-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem; gap: 1rem; }
    .dt-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 1.2rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.04); }
    .dataTables_wrapper .dataTables_length { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: #94a3b8; }
    .dataTables_wrapper .dataTables_filter { display: flex; align-items: center; }
    .dataTables_wrapper .dataTables_filter label { display: flex; align-items: center; gap: 0.5rem; }
</style>
@endpush
