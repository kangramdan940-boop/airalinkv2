@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h5><i class="bi bi-arrow-left-right" style="color: var(--primary); margin-right: 0.5rem;"></i> Data Transfer Bank</h5>
    </div>
    <div class="card-body">
        {{-- Date Filter --}}
        <div style="display: flex; align-items: flex-end; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; padding: 1.2rem; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 14px;">
            <div>
                <label style="font-size: 0.72rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem; display: block;">Mulai Tanggal</label>
                <div class="date-input-wrap">
                    <i class="bi bi-calendar3"></i>
                    <input type="text" id="startDate" class="date-input" placeholder="Pilih tanggal" readonly>
                </div>
            </div>
            <div>
                <label style="font-size: 0.72rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.4rem; display: block;">Sampai Tanggal</label>
                <div class="date-input-wrap">
                    <i class="bi bi-calendar3"></i>
                    <input type="text" id="endDate" class="date-input" placeholder="Pilih tanggal" readonly>
                </div>
            </div>
            <div>
                <button type="button" class="btn btn-primary" id="btnFilter" style="height: 42px;">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
            <div>
                <button type="button" class="btn btn-outline-primary" id="btnReset" style="height: 42px;">
                    <i class="bi bi-x-lg"></i> Reset
                </button>
            </div>
        </div>

        {{-- Table --}}
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

<!-- Modal Edit -->
<div class="modal fade" id="formModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Edit Transfer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalBody"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
var dataTable;
var pageUrl = '{{ url($pageUrl) }}';
var baseUrl = pageUrl.replace(/\/+$/, '');
var modal = new bootstrap.Modal(document.getElementById('formModal'));

$(function() {
    dataTable = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: pageUrl + '/grid',
            type: 'POST',
            data: function(d) {
                d.start_date = $('#startDate').val();
                d.end_date = $('#endDate').val();
            }
        },
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

    $('#btnFilter').on('click', function() { dataTable.ajax.reload(); });
    $('#btnReset').on('click', function() {
        $('#startDate').val('');
        $('#endDate').val('');
        startPicker.clear();
        endPicker.clear();
        dataTable.ajax.reload();
    });

    // Edit button -> load form into modal
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        $.get(baseUrl + '/' + id + '/edit')
            .done(function(html) {
                var content = $(html).find('#modalBody').html();
                $('#modalBody').html(content || html);
                $('#modalTitle').text('Edit Transfer');
                modal.show();
            })
            .fail(function() {
                Swal.fire({ title: 'Error!', text: 'Gagal memuat formulir.', icon: 'error', background: '#1e293b', color: '#f1f5f9' });
            });
    });

    // Modal form submit (update)
    $(document).on('submit', '#formModal form', function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = new FormData(this);
        if (formData.get('nominal')) formData.set('nominal', formData.get('nominal').replace(/,/g, ''));
        if (formData.get('admin')) formData.set('admin', formData.get('admin').replace(/,/g, ''));

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method') || 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                modal.hide();
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

    // Clear modal body on close
    $('#formModal').on('hidden.bs.modal', function() {
        $('#modalBody').html('');
    });
});

// Flatpickr init
var startPicker = flatpickr('#startDate', { dateFormat: 'Y-m-d', disableMobile: true });
var endPicker = flatpickr('#endDate', { dateFormat: 'Y-m-d', disableMobile: true });
</script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@endpush

@push('styles')
<style>
    .dt-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem; gap: 1rem; }
    .dt-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 1.2rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.04); }
    .dataTables_wrapper .dataTables_length { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: #94a3b8; }
    .dataTables_wrapper .dataTables_filter { display: flex; align-items: center; }
    .dataTables_wrapper .dataTables_filter label { display: flex; align-items: center; gap: 0.5rem; }

    .date-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .date-input-wrap i {
        position: absolute;
        left: 12px;
        color: #60a5fa;
        font-size: 0.9rem;
        pointer-events: none;
        z-index: 1;
    }
    .date-input {
        padding: 0.6rem 0.9rem 0.6rem 2.4rem;
        background: rgba(255, 255, 255, 0.04);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        color: #f1f5f9;
        font-size: 0.85rem;
        font-family: 'Inter', sans-serif;
        min-width: 170px;
        height: 42px;
        transition: all 0.25s ease;
        cursor: pointer;
    }
    .date-input:focus {
        outline: none;
        border-color: rgba(59, 130, 246, 0.5);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        background: rgba(59, 130, 246, 0.05);
    }

    /* Flatpickr Dark Theme Override */
    .flatpickr-calendar {
        background: rgba(30, 41, 59, 0.95) !important;
        backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255,255,255,0.1) !important;
        border-radius: 16px !important;
        box-shadow: 0 20px 50px rgba(0,0,0,0.5) !important;
        font-family: 'Inter', sans-serif !important;
    }
    .flatpickr-months { border-radius: 16px 16px 0 0; overflow: hidden; }
    .flatpickr-months .flatpickr-month { background: rgba(0,0,0,0.2) !important; color: #f1f5f9 !important; }
    .flatpickr-current-month .flatpickr-monthDropdown-months { background: transparent !important; color: #f1f5f9 !important; }
    .flatpickr-current-month input.cur-year { color: #f1f5f9 !important; }
    .flatpickr-months .flatpickr-prev-month, .flatpickr-months .flatpickr-next-month { color: #f1f5f9 !important; fill: #f1f5f9 !important; }
    .flatpickr-months .flatpickr-prev-month svg, .flatpickr-months .flatpickr-next-month svg { fill: #f1f5f9 !important; }
    .flatpickr-months .flatpickr-prev-month:hover, .flatpickr-months .flatpickr-next-month:hover { color: #60a5fa !important; }
    .flatpickr-months .flatpickr-prev-month:hover svg, .flatpickr-months .flatpickr-next-month:hover svg { fill: #60a5fa !important; }
    span.flatpickr-weekday { color: #64748b !important; font-weight: 600 !important; font-size: 0.75rem !important; }
    .flatpickr-day {
        color: #cbd5e1 !important;
        border-radius: 8px !important;
        font-size: 0.82rem !important;
        border: none !important;
    }
    .flatpickr-day:hover { background: rgba(59,130,246,0.15) !important; border-color: transparent !important; }
    .flatpickr-day.selected, .flatpickr-day.selected:hover {
        background: #3b82f6 !important;
        color: #fff !important;
        border-color: transparent !important;
        box-shadow: 0 4px 12px rgba(59,130,246,0.3) !important;
    }
    .flatpickr-day.today { color: #60a5fa !important; font-weight: 700 !important; }
    .flatpickr-day.today:hover { background: rgba(59,130,246,0.15) !important; }
    .flatpickr-day.flatpickr-disabled { color: #334155 !important; }
    .flatpickr-day.prevMonthDay, .flatpickr-day.nextMonthDay { color: #475569 !important; }
    .flatpickr-innerContainer { border-bottom: none !important; }
    .flatpickr-rContainer { padding: 0.5rem !important; }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush
