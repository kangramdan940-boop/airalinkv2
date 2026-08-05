<div id="modalBody">
<form action="{{ url($pageUrl . '/' . $record->id) }}" method="POST">
    @csrf
    @method('PUT')
    <input type="hidden" name="id" value="{{ $record->id }}">
    <div class="mb-3">
        <label class="form-label">Nama Pegawai <span class="text-danger">*</span></label>
        <input type="text" name="nama" class="form-control" value="{{ $record->nama }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Telepon</label>
        <input type="text" name="telepon" class="form-control" value="{{ $record->telepon }}" maxlength="15">
    </div>
    <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="0" {{ $record->status == 0 ? 'selected' : '' }}>Probation</option>
            <option value="1" {{ $record->status == 1 ? 'selected' : '' }}>Aktif</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Keterangan</label>
        <textarea name="keterangan" class="form-control" rows="3">{{ $record->keterangan }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary w-100">Update</button>
</form>
</div>
