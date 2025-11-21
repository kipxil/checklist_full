@extends('layouts.mantis')

@section('header')
    {{-- ... Breadcrumb ... --}}
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Edit User</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
                        <li class="breadcrumb-item" aria-current="page">Edit</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5>Edit Details</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Employee ID</label>
                                <input type="text" name="nik" class="form-control"
                                    value="{{ old('nik', $user->nik) }}" required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">New Password <span class="text-muted fw-normal">(Leave empty to
                                        keep current)</span></label>
                                <input type="password" name="password" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select" required>
                                    {{-- Ambil role pertama user ini --}}
                                    @php $currentRole = $user->roles->first()->name ?? ''; @endphp

                                    @foreach ($roles as $role)
                                        <option value="{{ $role->name }}"
                                            {{ old('role', $currentRole) == $role->name ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Assigned Restaurants (Cluster Access)</label>
                                <div class="card border bg-light">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            @foreach ($restaurants as $rest)
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="restaurants[]"
                                                            value="{{ $rest->id }}" id="rest_{{ $rest->id }}"
                                                            {{-- Cek apakah ID resto ini ada di relasi user --}}
                                                            {{ $user->restaurants->contains($rest->id) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="rest_{{ $rest->id }}">
                                                            {{ $rest->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">Update User</button>
                            <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
