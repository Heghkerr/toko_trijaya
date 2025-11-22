@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Edit Profil</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row mb-4">
                <div class="col-md-4 text-center">
                    <div class="mb-3">
                        <img src="{{ Auth::user()->photo_url }}" alt="Foto Profil"
                             class="img-thumbnail rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                    </div>
                    <div class="form-group">
                        <label for="photo" class="btn btn-outline-primary">
                            <i class="bi bi-camera"></i> Pilih Foto
                            <input type="file" id="photo" name="photo" class="d-none"
                                   onchange="document.getElementById('photo-preview').src = window.URL.createObjectURL(this.files[0])">
                        </label>
                        <small class="form-text text-muted d-block">Format: JPEG, PNG, JPG (max 2MB)</small>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group mb-3">
                        <label for="name">name</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="{{ old('name', Auth::user()->name) }}" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="{{ old('email', Auth::user()->email) }}" required>
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-3">Ubah Password</h5>

                    <div class="form-group mb-3">
                        <label for="current_password">Password Saat Ini</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>

                    <div class="form-group mb-3">
                        <label for="new_password">Password Baru</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                    </div>

                    <div class="form-group mb-3">
                        <label for="new_password_confirmation">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="new_password_confirmation"
                               name="new_password_confirmation">
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Preview foto sebelum upload
    document.getElementById('photo').addEventListener('change', function(e) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.querySelector('.img-thumbnail').src = event.target.result;
        };
        reader.readAsDataURL(e.target.files[0]);
    });
</script>
@endpush
