@extends('layouts.mantis')

@section('header')
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Edit</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('profile.edit') }}">Profile</a></li>
                        <li class="breadcrumb-item" aria-current="page">Edit Password</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <h2 class="mb-4">Edit Password</h2>

    {{-- Card untuk Update Informasi Profil --}}

    {{-- Card untuk Update Password --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ __('Update Password') }}</h5>
        </div>
        <div class="card-body">
            <p class="card-text text-muted">
                {{ __('Ensure your account is using a long, random password to stay secure.') }}</p>

            <form method="post" action="{{ route('password.update') }}" class="mt-4">
                @csrf
                @method('put')

                <div class="mb-3">
                    <label for="current_password" class="form-label">{{ __('Current Password') }}</label>
                    <input id="current_password" name="current_password" type="password"
                        class="form-control @error('current_password', 'updatePassword') is-invalid @enderror">
                    @error('current_password', 'updatePassword')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">{{ __('New Password') }}</label>
                    <input id="password" name="password" type="password"
                        class="form-control @error('password', 'updatePassword') is-invalid @enderror">
                    @error('password', 'updatePassword')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control">
                </div>

                <div class="d-flex align-items-center gap-4">
                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    @if (session('status') === 'password-updated')
                        <p class="text-success">{{ __('Saved.') }}</p>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Card untuk Hapus Akun --}}
    {{-- <div class="card border-danger">
        <div class="card-header bg-danger text-white">
            <h5 class="card-title mb-0">{{ __('Delete Account') }}</h5>
        </div>
        <div class="card-body">
            <p class="card-text text-muted">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted.') }}</p>

            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
                {{ __('Delete Account') }}
            </button>
        </div>
    </div> --}}

    <div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmUserDeletionModalLabel">
                            {{ __('Are you sure you want to delete your account?') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">
                            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                        </p>
                        <div class="mt-3">
                            <label for="password_delete" class="form-label visually-hidden">{{ __('Password') }}</label>
                            <input id="password_delete" name="password" type="password"
                                class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                                placeholder="{{ __('Password') }}">
                            @error('password', 'userDeletion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-danger">{{ __('Delete Account') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
