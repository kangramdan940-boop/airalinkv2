@extends('layouts.app')

@php
    use App\Models\Transaksi\Transfer;
    use Carbon\Carbon;

    $userId = auth()->id();
    $records = Transfer::where('user_id', $userId)
        ->whereDate('created_at', Carbon::today())
        ->orderBy('created_at', 'desc')
        ->get();
    $total = $records->sum('nominal');
@endphp

@section('content')
<div class="tf-layout">

    {{-- Tombol Buka Form (di atas) --}}
    <div class="tf-toggle-wrap" id="toggleWrap">
        <button class="tf-btn-open" onclick="toggleForm()">
            <i class="bi bi-plus-circle-fill"></i> Lakukan Transaksi
        </button>
    </div>

    {{-- Form Section (collapsed by default) --}}
    <div class="tf-form-card" id="formCard" style="display: none;">
        <div class="tf-form-header">
            <h4><i class="bi bi-send"></i> Request Transfer</h4>
            <button class="tf-btn-close-form" onclick="toggleForm()"><i class="bi bi-x-lg"></i></button>
        </div>

        <form id="transferForm" method="POST" action="{{ url('transfer') }}">
            @csrf

            {{-- Bank --}}
            <div class="tf-field">
                <label>Bank Tujuan</label>
                <select name="kode_bank" id="kode_bank" class="form-select">
                    <option value="">-- Pilih Bank --</option>
                    @foreach(config('banks') as $bank)
                        <option value="{{ $bank['code'] }}-{{ $bank['name'] }}">{{ $bank['name'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- No Rekening --}}
            <div class="tf-field">
                <label>Nomor Rekening</label>
                <select name="no_rekening" id="no_rekening" class="form-select no-select2" required>
                </select>
            </div>

            {{-- Atas Nama --}}
            <div class="tf-field">
                <label>Atas Nama</label>
                <input type="text" name="atas_nama" id="atas_nama" class="form-control" placeholder="Nama pemilik rekening" required style="text-transform: uppercase;">
            </div>

            {{-- Nominal & Admin --}}
            <div class="tf-row">
                <div class="tf-field">
                    <label>Nominal Transfer</label>
                    <div class="tf-input-money">
                        <span>Rp</span>
                        <input type="text" name="nominal" class="form-control" placeholder="0" required data-type="currency" inputmode="numeric">
                    </div>
                </div>
                <div class="tf-field tf-field-small">
                    <label>Admin</label>
                    <div class="tf-input-money">
                        <span>Rp</span>
                        <input type="text" name="admin" class="form-control" placeholder="0" required data-type="currency" inputmode="numeric">
                    </div>
                </div>
            </div>

            {{-- Keterangan --}}
            <div class="tf-field">
                <label>Keterangan</label>
                <select name="keterangan" class="form-select no-select2">
                    <option value="Uang sudah di hitung">Uang sudah di hitung</option>
                    <option value="Uang sudah pas">Uang sudah pas</option>
                    <option value="Nama Ditulis manual">Nama ditulis manual</option>
                    <option value="Ada uang sobek">Ada uang sobek</option>
                    <option value="Ada Uang kurang">Ada uang kurang</option>
                    <option value="Uang sudah aman">Uang sudah aman</option>
                </select>
            </div>

            {{-- Submit --}}
            <button type="submit" class="tf-btn-submit">
                <i class="bi bi-send-fill"></i> Kirim Transfer
            </button>
        </form>
    </div>

    {{-- History Section (di bawah, selalu terlihat) --}}
    <div class="tf-history-card">
        <div class="tf-history-header">
            <h5><i class="bi bi-clock-history"></i> Hari Ini ({{ $records->count() }})</h5>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <span class="tf-today-badge">Rp {{ number_format($total, 0, ',', '.') }}</span>
                <button class="tf-btn-refresh" onclick="window.location.reload()"><i class="bi bi-arrow-clockwise"></i></button>
            </div>
        </div>
        <div class="tf-history-list">
            @forelse($records as $v)
            <div class="tf-history-item" id="item-{{ $v->id }}">
                <div class="tf-history-left">
                    <div class="tf-history-name">{{ strtoupper($v->atas_nama) }}</div>
                    <div class="tf-history-bank">{{ $v->kode_bank }} • {{ $v->no_rekening }}</div>
                </div>
                <div class="tf-history-right">
                    <div class="tf-history-amount">Rp {{ number_format($v->nominal, 0, ',', '.') }}</div>
                    <div class="tf-history-status">
                        @if($v->status == 1)
                            <span class="st-request">Request</span>
                            @php
                                $waText = "*REQUEST TRANSFER*\n\n"
                                    . "Kode: #TF-" . $v->id . "\n"
                                    . "Bank: " . $v->kode_bank . "\n"
                                    . "No Rek: _" . $v->no_rekening . "_\n"
                                    . "Atas Nama: *" . strtoupper($v->atas_nama) . "*\n"
                                    . "Nominal: *Rp " . number_format($v->nominal, 0, ',', '.') . "*\n"
                                    . "Admin: Rp " . number_format($v->admin, 0, ',', '.') . "\n"
                                    . "Ket: " . $v->keterangan . "\n\n"
                                    . "Dari: *" . auth()->user()->username . "*";
                                $waUrl = "https://wa.me/6285775772673?text=" . urlencode($waText);
                            @endphp
                            <a href="{{ $waUrl }}" target="_blank" class="tf-btn-wa" id="wa-{{ $v->id }}" onclick="markSent({{ $v->id }})" title="Kirim ke WhatsApp">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                            <small class="wa-sent-label" id="wa-label-{{ $v->id }}" style="display:none; color:#64748b; font-size:0.65rem;">sudah dikirim</small>
                        @elseif($v->status == 2)<span class="st-process">Process</span>
                        @elseif($v->status == 3)<span class="st-success">Success</span>
                        @elseif($v->status == 4)<span class="st-cancel">Cancel</span>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="tf-history-empty">
                <i class="bi bi-inbox"></i>
                <p>Belum ada transaksi hari ini</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
    .tf-layout { display: flex; flex-direction: column; gap: 1.5rem; max-width: 700px; }

    .tf-today-badge {
        font-size: 0.85rem;
        font-weight: 700;
        color: #fbbf24;
        background: rgba(245,158,11,0.1);
        padding: 4px 10px;
        border-radius: 8px;
    }

    .tf-toggle-wrap { text-align: center; }
    .tf-btn-open {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, rgba(59,130,246,0.1), rgba(139,92,246,0.08));
        border: 1px dashed rgba(59,130,246,0.3);
        border-radius: 14px;
        color: #60a5fa;
        font-size: 0.95rem;
        font-weight: 600;
        font-family: 'Inter', sans-serif;
        cursor: pointer;
        transition: all 0.3s;
    }
    .tf-btn-open:hover {
        background: linear-gradient(135deg, rgba(59,130,246,0.15), rgba(139,92,246,0.12));
        border-color: rgba(59,130,246,0.5);
        transform: translateY(-1px);
    }
    .tf-btn-open i { margin-right: 0.4rem; }

    .tf-btn-close-form {
        width: 32px; height: 32px;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,0.1);
        background: rgba(255,255,255,0.03);
        color: #94a3b8;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .tf-btn-close-form:hover { color: #ef4444; border-color: rgba(239,68,68,0.3); }

    .tf-form-card, .tf-history-card {
        background: rgba(255,255,255,0.03);
        backdrop-filter: blur(40px);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 20px;
        overflow: hidden;
    }

    .tf-form-header {
        padding: 1.2rem 1.5rem;
        background: rgba(0,0,0,0.15);
        border-bottom: 1px solid rgba(255,255,255,0.06);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .tf-form-header h4 { font-size: 1rem; font-weight: 700; color: #f1f5f9; margin: 0; }
    .tf-form-header h4 i { color: #3b82f6; margin-right: 0.4rem; }

    .tf-form-card form { padding: 1.5rem; }

    .tf-field { margin-bottom: 1.2rem; }
    .tf-field label {
        display: block;
        font-size: 0.78rem;
        font-weight: 600;
        color: #94a3b8;
        margin-bottom: 0.4rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .tf-row { display: grid; grid-template-columns: 1fr 240px; gap: 0.8rem; }
    @media (max-width: 500px) { .tf-row { grid-template-columns: 1fr 180px; } }

    .tf-input-money {
        position: relative;
        display: flex;
        align-items: center;
    }
    .tf-input-money span {
        position: absolute;
        left: 12px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #64748b;
        pointer-events: none;
    }
    .tf-input-money input {
        padding-left: 2.2rem !important;
        font-weight: 700 !important;
        font-size: 1rem !important;
        letter-spacing: 0.5px;
    }

    .tf-btn-submit {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        border: none;
        border-radius: 14px;
        color: #fff;
        font-size: 1rem;
        font-weight: 700;
        font-family: 'Inter', sans-serif;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 0.5rem;
    }
    .tf-btn-submit:hover { box-shadow: 0 8px 24px rgba(59,130,246,0.4); transform: translateY(-1px); }
    .tf-btn-submit:active { transform: translateY(0); }

    /* History */
    .tf-history-header {
        padding: 1rem 1.5rem;
        background: rgba(0,0,0,0.15);
        border-bottom: 1px solid rgba(255,255,255,0.06);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .tf-history-header h5 { font-size: 0.9rem; font-weight: 600; color: #f1f5f9; margin: 0; }
    .tf-history-header h5 i { color: #3b82f6; margin-right: 0.3rem; }
    .tf-btn-refresh {
        width: 32px; height: 32px;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,0.1);
        background: rgba(255,255,255,0.03);
        color: #94a3b8;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .tf-btn-refresh:hover { color: #3b82f6; border-color: rgba(59,130,246,0.3); }

    .tf-history-list { max-height: 500px; overflow-y: auto; }
    .tf-history-item {
        padding: 0.9rem 1.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.03);
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.2s;
    }
    .tf-history-item:hover { background: rgba(59,130,246,0.03); }
    .tf-history-item.sent {
        background: rgba(37, 211, 102, 0.04);
        border-left: 3px solid rgba(37, 211, 102, 0.3);
    }
    .tf-history-name { font-size: 0.85rem; font-weight: 600; color: #f1f5f9; }
    .tf-history-bank { font-size: 0.72rem; color: #64748b; margin-top: 2px; }
    .tf-history-right { text-align: right; }
    .tf-history-amount { font-size: 0.85rem; font-weight: 700; color: #f1f5f9; }
    .tf-history-status { margin-top: 3px; }
    .tf-history-status span { font-size: 0.68rem; padding: 2px 8px; border-radius: 20px; font-weight: 600; }
    .st-request { background: rgba(59,130,246,0.12); color: #60a5fa; }
    .st-process { background: rgba(245,158,11,0.12); color: #fbbf24; }
    .st-success { background: rgba(16,185,129,0.12); color: #34d399; }
    .st-cancel { background: rgba(100,116,139,0.15); color: #94a3b8; }

    .tf-history-empty { padding: 3rem; text-align: center; color: #475569; }
    .tf-history-empty i { font-size: 2rem; display: block; margin-bottom: 0.5rem; }
    .tf-history-empty p { margin: 0; font-size: 0.85rem; }

    .tf-btn-wa {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px; height: 26px;
        background: rgba(37, 211, 102, 0.15);
        border: 1px solid rgba(37, 211, 102, 0.3);
        border-radius: 6px;
        color: #25d366;
        font-size: 0.8rem;
        text-decoration: none;
        margin-left: 6px;
        transition: all 0.2s;
    }
    .tf-btn-wa:hover {
        background: rgba(37, 211, 102, 0.25);
        color: #25d366;
        transform: scale(1.1);
    }
    .tf-btn-wa.sent {
        background: rgba(100, 116, 139, 0.1);
        border-color: rgba(100, 116, 139, 0.2);
        color: #64748b;
        opacity: 0.7;
    }
    .tf-btn-wa.sent:hover {
        background: rgba(100, 116, 139, 0.15);
        color: #64748b;
        transform: none;
    }
</style>
@endpush

@push('scripts')
<script>
// Toggle form visibility
function toggleForm() {
    var form = document.getElementById('formCard');
    var toggle = document.getElementById('toggleWrap');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        toggle.style.display = 'none';
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        form.style.display = 'none';
        toggle.style.display = 'block';
    }
}

// WA sent tracking
function markSent(id) {
    localStorage.setItem('wa_sent_' + id, '1');
    $('#wa-' + id).addClass('sent');
    $('#wa-label-' + id).show();
    $('#item-' + id).addClass('sent');
}
// Restore sent state on load
$(function() {
    $('[id^="wa-"][class*="tf-btn-wa"]').each(function() {
        var id = $(this).attr('id').replace('wa-', '');
        if (localStorage.getItem('wa_sent_' + id)) {
            $(this).addClass('sent');
            $('#wa-label-' + id).show();
            $('#item-' + id).addClass('sent');
        }
    });
});
// Currency formatting
$("input[data-type='currency']").on({
    keyup: function() { formatCurrency($(this)); },
    blur: function() { formatCurrency($(this), "blur"); }
});
function formatCurrency(input, blur) {
    var val = input.val().replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    input.val(val);
}

// Select2 AJAX untuk No Rekening (autocomplete + free text)
$('#no_rekening').select2({
    placeholder: 'Ketik nomor rekening...',
    allowClear: true,
    tags: true,
    minimumInputLength: 5,
    ajax: {
        url: '{{ url("rekening-valid/lookup-search") }}',
        dataType: 'json',
        delay: 300,
        data: function(params) {
            return { q: params.term };
        },
        processResults: function(data) {
            return { results: data };
        }
    },
    createTag: function(params) {
        var term = $.trim(params.term);
        if (term === '') return null;
        return { id: term, text: term, newTag: true };
    },
    templateResult: function(data) {
        if (data.loading) return 'Mencari...';
        if (data.newTag) return $('<span><i class="bi bi-plus-circle" style="margin-right:5px;color:#60a5fa"></i>' + data.text + ' <small style="color:#64748b">(input manual)</small></span>');
        return $('<span><strong>' + data.text + '</strong> <small style="color:#94a3b8">— ' + (data.nama || '') + '</small></span>');
    },
    templateSelection: function(data) {
        return data.text;
    }
}).on('select2:select', function(e) {
    var data = e.params.data;
    if (data.atas_nama) {
        $('#atas_nama').val(data.atas_nama);
    } else {
        // Input manual - clear atas nama
        $('#atas_nama').val('');
    }
});

// Form submit
$('#transferForm').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            Swal.fire({ title: 'Berhasil!', text: res.message, icon: 'success', background: '#1e293b', color: '#f1f5f9', confirmButtonColor: '#3b82f6' }).then(() => {
                window.location.reload();
            });
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
