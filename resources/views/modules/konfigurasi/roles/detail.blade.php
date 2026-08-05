@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h5><i class="bi bi-shield-fill-check" style="color: var(--primary); margin-right: 0.5rem;"></i> Permissions: {{ ucfirst($record->name) }}</h5>
        <div style="display: flex; gap: 0.5rem;">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="checkAll()">
                <i class="bi bi-check-all"></i> Check All
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="uncheckAll()">
                <i class="bi bi-x-lg"></i> Uncheck All
            </button>
            <a href="{{ url('konfigurasi/roles') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <form id="grantForm">
            @csrf
            @method('PUT')

            <div class="perm-grid">
                @foreach($permissions as $group => $perms)
                <div class="perm-group">
                    <div class="perm-group-header">
                        <label class="perm-check-all">
                            <input type="checkbox" onchange="toggleGroup(this, '{{ $group }}')" 
                                {{ $perms->every(fn($p) => $record->hasPermissionTo($p->name)) ? 'checked' : '' }}>
                            <span>{{ ucwords(str_replace('-', ' ', $group)) }}</span>
                        </label>
                    </div>
                    <div class="perm-group-body">
                        @foreach($perms as $perm)
                        <label class="perm-item">
                            <input type="checkbox" name="check[]" value="{{ $perm->name }}" class="perm-cb" data-group="{{ $group }}"
                                {{ $record->hasPermissionTo($perm->name) ? 'checked' : '' }}>
                            <span>{{ ucfirst(last(explode('-', $perm->name))) }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 1.5rem;">
                <i class="bi bi-check-lg"></i> Simpan Permissions
            </button>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .perm-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    @media (max-width: 768px) { .perm-grid { grid-template-columns: 1fr; } }

    .perm-group {
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 12px;
        overflow: hidden;
    }
    .perm-group-header {
        padding: 0.8rem 1rem;
        background: rgba(0,0,0,0.15);
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .perm-check-all {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        color: #f1f5f9;
    }
    .perm-check-all input {
        width: 18px; height: 18px;
        accent-color: #3b82f6;
        cursor: pointer;
    }
    .perm-group-body {
        padding: 0.8rem 1rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .perm-item {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.8rem;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.8rem;
        color: #cbd5e1;
        transition: all 0.2s;
    }
    .perm-item:hover {
        background: rgba(59,130,246,0.08);
        border-color: rgba(59,130,246,0.2);
    }
    .perm-item:has(input:checked) {
        background: rgba(59,130,246,0.12);
        border-color: rgba(59,130,246,0.3);
        color: #60a5fa;
    }
    .perm-item input {
        width: 15px; height: 15px;
        accent-color: #3b82f6;
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script>
function toggleGroup(el, group) {
    var checked = el.checked;
    document.querySelectorAll('.perm-cb[data-group="' + group + '"]').forEach(function(cb) {
        cb.checked = checked;
    });
}

function checkAll() {
    document.querySelectorAll('.perm-cb').forEach(function(cb) { cb.checked = true; });
    document.querySelectorAll('.perm-check-all input').forEach(function(cb) { cb.checked = true; });
}

function uncheckAll() {
    document.querySelectorAll('.perm-cb').forEach(function(cb) { cb.checked = false; });
    document.querySelectorAll('.perm-check-all input').forEach(function(cb) { cb.checked = false; });
}

// Update group header checkbox when individual items change
$(document).on('change', '.perm-cb', function() {
    var group = $(this).data('group');
    var all = document.querySelectorAll('.perm-cb[data-group="' + group + '"]');
    var checked = document.querySelectorAll('.perm-cb[data-group="' + group + '"]:checked');
    var header = $(this).closest('.perm-group').find('.perm-check-all input');
    header.prop('checked', all.length === checked.length);
});

$('#grantForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: '{{ url("konfigurasi/roles/" . $record->id . "/grant") }}',
        type: 'PUT',
        data: $(this).serialize(),
        success: function(res) {
            Swal.fire({ title: 'Berhasil!', text: res.message || 'Permissions diupdate.', icon: 'success', background: '#1e293b', color: '#f1f5f9', confirmButtonColor: '#3b82f6' });
        },
        error: function() {
            Swal.fire({ title: 'Error!', text: 'Gagal menyimpan.', icon: 'error', background: '#1e293b', color: '#f1f5f9' });
        }
    });
});
</script>
@endpush
