@extends('layouts.mantis')

@section('header')
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Master Restaurants</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('daily-reports.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">Settings</li>
                        <li class="breadcrumb-item" aria-current="page">Restaurants</li>
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

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Restaurant List</h5>
                    <a href="{{ route('restaurants.create') }}" class="btn btn-sm btn-primary">
                        <i class="ti ti-plus"></i> Add New
                    </a>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="10%">Code</th>
                                    <th>Restaurant Name</th>
                                    <th width="15%" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($restaurants as $rest)
                                    <tr>
                                        <td><span class="badge bg-light-dark text-dark border">{{ $rest->code }}</span>
                                        </td>
                                        <td class="fw-bold">{{ $rest->name }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('restaurants.edit', $rest->id) }}"
                                                class="btn btn-icon btn-link-warning btn-sm">
                                                <i class="ti ti-pencil"></i>
                                            </a>
                                            <form action="{{ route('restaurants.destroy', $rest->id) }}" method="POST"
                                                class="d-inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-icon btn-link-danger btn-sm"
                                                    onclick="return confirm('Hapus restoran {{ $rest->name }}? PERINGATAN: Semua laporan terkait restoran ini akan ikut terhapus!')">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
