@extends('layouts.mantis')

@section('header')
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Daily Reports - Restaurants</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
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

            {{-- Alert Sukses --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Recent Reports</h5>
                    {{-- TOMBOL MENUJU HALAMAN CREATE --}}
                    <a href="{{ route('daily-reports.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus"></i> Create New Report
                    </a>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Report Date</th>
                                    <th>Created At</th>
                                    <th>Restaurant</th>
                                    <th>Created By</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $report)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $report->date->format('d M Y') }}</div>
                                            <div class="small text-muted">{{ $report->date->format('H:i') }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $report->created_at->format('d M Y') }}</div>
                                            <div class="small text-muted">{{ $report->created_at->format('H:i') }}</div>
                                        </td>
                                        <td>{{ $report->restaurant->name }}</td>
                                        <td>{{ $report->user->name }}</td>
                                        <td>
                                            @if ($report->status == 'approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($report->status == 'submitted')
                                                <span class="badge bg-primary">Submitted</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Draft</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{-- Link ke Detail (Show) --}}
                                            <a href="{{ route('daily-reports.show', $report->id) }}"
                                                class="btn btn-icon btn-link-secondary">
                                                <i class="ti ti-eye"></i>
                                            </a>

                                            {{-- Tombol Edit (Hanya Draft) --}}
                                            @if ($report->status == 'draft')
                                                <a href="{{ route('daily-reports.edit', $report->id) }}"
                                                    class="btn btn-icon btn-link-warning">
                                                    <i class="ti ti-pencil"></i>
                                                </a>
                                            @endif

                                            {{-- TOMBOL HAPUS (Hanya Manager & Super Admin) --}}
                                            @hasanyrole('Super Admin|Restaurant Manager')
                                                <form action="{{ route('daily-reports.destroy', $report->id) }}" method="POST"
                                                    class="d-inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-icon btn-link-danger"
                                                        onclick="return confirm('Apakah Anda yakin ingin menghapus laporan tanggal {{ $report->date->format('d M Y') }} ini? Tindakan ini tidak bisa dibatalkan.')">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            @endhasanyrole
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            No reports found. Start by creating one!
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        {{-- Pagination --}}
                        <div class="mt-3">
                            {{ $reports->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
