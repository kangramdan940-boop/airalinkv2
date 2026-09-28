@extends('layouts.app')

@section('content')
<div id="modalBody">
<form action="{{ url($pageUrl . '/' . $record->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <input type="hidden" name="id" value="{{ $record->id }}">

    @php
        $knownBanks = ['3010109557','7391383182','5425162826','788801011010530','788801020276539','1277883403','1560019173766'];
    @endphp

    <div class="mb-3">
        <label class="form-label">Rekening Tujuan <span class="text-danger">*</span></label>
        <select name="kode_bank" class="form-select">
            @if(!in_array($record->kode_bank, $knownBanks))
                <option value="{{ $record->kode_bank }}" selected>{{ $record->no_rekening }} {{ $record->atas_nama }}</option>
            @endif
            <option value="3010109557" {{ ($record->kode_bank == '3010109557') ? 'selected' : '' }}>3010109557 M Ramdan Gumelar</option>
            <option value="7391383182" {{ ($record->kode_bank == '7391383182') ? 'selected' : '' }}>7391383182 M Ramdan Gumelar</option>
            <option value="5425162826" {{ ($record->kode_bank == '5425162826') ? 'selected' : '' }}>5425162826 Aida Hlimatusadiah</option>
            <option value="788801011010530" {{ ($record->kode_bank == '788801011010530') ? 'selected' : '' }}>788801011010530 Aida Hlimatusadiah</option>
            <option value="788801020276539" {{ ($record->kode_bank == '788801020276539') ? 'selected' : '' }}>788801020276539 M Ramdan Gumelar</option>
            <option value="1277883403" {{ ($record->kode_bank == '1277883403') ? 'selected' : '' }}>1277883403 M Ramdan Gumelar</option>
            <option value="1560019173766" {{ ($record->kode_bank == '1560019173766') ? 'selected' : '' }}>1560019173766 M Ramdan Gumelar</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Atas Nama</label>
        <input type="text" name="atas_nama" class="form-control" value="{{ $record->atas_nama }}" style="text-transform: uppercase;">
    </div>

    <div class="mb-3">
        <label class="form-label">Nominal <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="text" name="nominal" class="form-control" value="{{ $record->nominal }}" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Admin</label>
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="text" name="admin" class="form-control" value="{{ $record->admin }}">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Cabang Toko</label>
        <input type="text" name="cabang_toko" class="form-control" value="{{ $record->cabang_toko }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Keterangan</label>
        <textarea name="keterangan" class="form-control" rows="3">{{ $record->keterangan }}</textarea>
    </div>

    @if($record->file)
    <div class="mb-3">
        <label class="form-label">Bukti Transfer Saat Ini</label>
        <a href="{{ asset($record->file) }}" target="_blank" class="d-block">Lihat bukti</a>
    </div>
    @endif

    <div class="mb-3">
        <label class="form-label">Ganti Bukti (Foto Struk)</label>
        <input type="file" name="file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
        <div class="form-text" style="font-size:0.72rem">Kosongkan jika tidak ingin mengganti bukti.</div>
    </div>

    <button type="submit" class="btn btn-primary w-100" id="btnSubmit"><i class="bi bi-check-lg"></i> Update</button>
</form>
</div>
@endsection
