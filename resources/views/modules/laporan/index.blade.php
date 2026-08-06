@extends('layouts.app')

@section('content')

<div style="max-width: 600px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <h5><i class="bi bi-file-earmark-pdf" style="color: #ef4444; margin-right: 0.5rem;"></i> Generate Laporan PDF</h5>
        </div>
        <div class="card-body" style="padding: 2rem;">
            <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 1.5rem;">
                Laporan berisi rekap keuntungan, pengeluaran, dan bagi hasil investor.
            </p>

            <form method="POST" action="{{ url('/laporan/generate') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label" style="color: #cbd5e1;">Karyawan</label>
                    <select name="user_id" class="form-select">
                        <option value="">Semua Karyawan</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->username }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">Bulan Mulai <span class="text-danger">*</span></label>
                        <div class="date-input-wrap">
                            <i class="bi bi-calendar3"></i>
                            <input type="text" name="bulan_mulai" id="bulanMulai" class="date-input" required readonly placeholder="Pilih bulan">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: #cbd5e1;">Bulan Akhir <span class="text-danger">*</span></label>
                        <div class="date-input-wrap">
                            <i class="bi bi-calendar3"></i>
                            <input type="text" name="bulan_akhir" id="bulanAkhir" class="date-input" required readonly placeholder="Pilih bulan">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.8rem; margin-top: 1rem;">
                    <i class="bi bi-download"></i> Download Laporan PDF
                </button>
            </form>
        </div>
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
        width: 100%;
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
flatpickr('#bulanMulai', {
    plugins: [new monthSelectPlugin({ shorthand: false, dateFormat: "Y-m", altFormat: "F Y" })],
    disableMobile: true,
});
flatpickr('#bulanAkhir', {
    plugins: [new monthSelectPlugin({ shorthand: false, dateFormat: "Y-m", altFormat: "F Y" })],
    disableMobile: true,
});
</script>
@endpush
