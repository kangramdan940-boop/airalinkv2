@extends('layouts.list')

@section('form-content')
<form action="{{ url($pageUrl) }}" method="POST">
    @csrf
    <div class="mb-3">
        <label class="form-label">Nama Role <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" required placeholder="contoh: admin, user, supervisor">
    </div>
    <button type="submit" class="btn btn-primary w-100">Simpan Role</button>
</form>
@endsection
