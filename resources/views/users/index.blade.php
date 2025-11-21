@extends('layouts.mantis')

@section('header')
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">User Management</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('daily-reports.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">Settings</li>
                        <li class="breadcrumb-item" aria-current="page">Users</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>User List</h5>
                    <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary">
                        <i class="ti ti-user-plus"></i> Add New User
                    </a>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Employee ID</th>
                                    <th>Role</th>
                                    <th>Restaurant</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset('template/dist') }}/assets/images/user/avatar-2.jpg"
                                                    alt="user" class="wid-30 rounded-circle me-2">
                                                <h6 class="mb-0">{{ $user->name }}</h6>
                                            </div>
                                        </td>
                                        <td>{{ $user->nik }}</td>
                                        <td>
                                            @foreach ($user->roles as $role)
                                                <span
                                                    class="badge bg-light-primary text-primary border border-primary">{{ $role->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @if ($user->hasRole('Super Admin'))
                                                <span class="badge bg-dark">Global Admin</span>
                                            @elseif($user->restaurants->isNotEmpty())
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach ($user->restaurants as $rest)
                                                        <span class="badge bg-light-secondary text-dark border">
                                                            {{ $rest->code }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-danger small fst-italic">No Access</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('users.edit', $user->id) }}"
                                                class="btn btn-icon btn-link-warning btn-sm">
                                                <i class="ti ti-pencil"></i>
                                            </a>
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                class="d-inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-icon btn-link-danger btn-sm"
                                                    onclick="return confirm('Hapus user {{ $user->name }}?')">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-3">{{ $users->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
