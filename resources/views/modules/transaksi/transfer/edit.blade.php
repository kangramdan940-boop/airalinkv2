@extends('layouts.app')

@section('content')
<div id="modalBody">
<form action="{{ url($pageUrl . '/' . $record->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <input type="hidden" name="id" value="{{ $record->id }}">

    <div class="mb-3">
        <label class="form-label">Kode Bank <span class="text-danger">*</span></label>
        <input type="text" name="kode_bank" class="form-control" value="{{ $record->kode_bank }}">
    </div>

    <div class="mb-3">
        <label class="form-label">No Rekening</label>
        <input type="text" name="no_rekening" class="form-control" value="{{ $record->no_rekening }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Atas Nama</label>
        <input type="text" name="atas_nama" class="form-control" value="{{ $record->atas_nama }}" style="text-transform: uppercase;">
    </div>

    <div class="mb-3">
        <label class="form-label">Nominal <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="text" name="nominal" class="form-control" value="{{ $record->nominal }}">
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

    <div class="mb-3">
        <label class="form-label">No Whatsapp</label>
        <input type="text" name="whatsapp" class="form-control" value="{{ $record->whatsapp }}">
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
