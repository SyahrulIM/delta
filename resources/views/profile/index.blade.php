@extends('layouts.app')

@section('title', 'Profile')
@section('page-title', 'Profile')

@push('styles')
@endpush

@section('content')
<section class="section">
    <div class="row">
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-center align-items-center flex-column">
                        <div class="avatar avatar-2xl">
                            <img src="{{ auth()->user()->avatar ?? asset('mazer/assets/compiled/jpg/1.jpg') }}" alt="Avatar">
                        </div>

                        <h3 class="mt-3">{{ auth()->user()->name }}</h3>
                        <p class="text-small">{{ auth()->user()->role }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form id="profileForm">
                        @csrf

                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ auth()->user()->name }}">
                            <div class="invalid-feedback" id="error-name"></div>
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ auth()->user()->email }}">
                            <div class="invalid-feedback" id="error-email"></div>
                        </div>

                        <hr>

                        <div class="form-group">
                            <label>Password Baru</label>
                            <input type="password" name="password" id="password" class="form-control">
                            <div class="invalid-feedback" id="error-password"></div>
                        </div>

                        <div class="form-group">
                            <label>Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<!-- Dependencies -->

<script>
$(function () {

    $('#profileForm').on('submit', function (e) {
        e.preventDefault();

        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $.ajax({
            url: "{{ route('profile.update') }}",
            method: "POST",
            data: $(this).serialize(),
            success: function (resp) {
                toastSuccess(resp.message || 'Profile updated');
                location.reload();
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;

                    for (let key in errors) {
                        $('#' + key).addClass('is-invalid');
                        $('#error-' + key).text(errors[key][0]);
                    }

                    toastWarning('Validation error');
                } else {
                    toastError('Terjadi kesalahan server');
                }
            }
        });

    });

});
</script>
@endpush
