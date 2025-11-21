@extends('layouts.mantis')

@section('header')
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Master Menu Items</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('daily-reports.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">Settings</li>
                        <li class="breadcrumb-item" aria-current="page">Master Menu</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">

            {{-- Alert Sukses --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5>Menu List</h5>

                        <div class="d-flex gap-2">
                            {{-- FILTER FORM --}}
                            <form action="{{ route('upselling-items.index') }}" method="GET" class="d-flex gap-2">
                                <select name="restaurant_id" class="form-select form-select-sm"
                                    onchange="this.form.submit()">
                                    <option value="">-- All Restaurants --</option>
                                    @foreach ($restaurants as $rest)
                                        <option value="{{ $rest->id }}"
                                            {{ request('restaurant_id') == $rest->id ? 'selected' : '' }}>
                                            {{ $rest->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>

                            {{-- TOMBOL TAMBAH --}}
                            <a href="{{ route('upselling-items.create') }}" class="btn btn-sm btn-primary">
                                <i class="ti ti-plus"></i> Add New Menu
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th>Restaurant</th>
                                    <th width="15%">Type</th>
                                    <th>Menu Name</th>
                                    <th width="15%" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $index => $item)
                                    <tr>
                                        <td>{{ $items->firstItem() + $index }}</td>
                                        <td>
                                            <span class="fw-bold">{{ $item->restaurant->name }}</span>
                                            <div class="small text-muted">{{ $item->restaurant->code }}</div>
                                        </td>
                                        <td>
                                            @if ($item->type == 'food')
                                                <span class="badge bg-light-primary text-primary">
                                                    <i class="ti ti-tools-kitchen-2 me-1"></i> Food
                                                </span>
                                            @else
                                                <span class="badge bg-light-success text-success">
                                                    <i class="ti ti-glass-full me-1"></i> Beverage
                                                </span>
                                            @endif
                                        </td>
                                        <td class="fw-bold">{{ $item->name }}</td>
                                        <td class="text-end">
                                            {{-- Tombol Edit --}}
                                            <a href="{{ route('upselling-items.edit', $item->id) }}"
                                                class="btn btn-icon btn-link-warning btn-sm">
                                                <i class="ti ti-pencil"></i>
                                            </a>

                                            {{-- Tombol Delete --}}
                                            <form action="{{ route('upselling-items.destroy', $item->id) }}" method="POST"
                                                class="d-inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-icon btn-link-danger btn-sm"
                                                    onclick="return confirm('Are you sure you want to delete {{ $item->name }}?')">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <div class="mb-2"><i class="ti ti-clipboard-off fs-1"></i></div>
                                            No menu items found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        {{-- Pagination --}}
                        <div class="mt-3">
                            {{ $items->withQueryString()->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
