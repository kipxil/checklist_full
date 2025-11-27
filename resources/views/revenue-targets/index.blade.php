@extends('layouts.mantis')

@section('header')
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Revenue Targets</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('daily-reports.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">Settings</li>
                        <li class="breadcrumb-item" aria-current="page">Targets</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">

        {{-- FORM INPUT (KIRI) --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Set Target</h5>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('revenue-targets.store') }}" method="POST">
                        @csrf

                        <div class="form-group mb-3">
                            <label class="form-label">Restaurant</label>
                            <select name="restaurant_id" class="form-select" required>
                                @foreach ($restaurants as $rest)
                                    <option value="{{ $rest->id }}">{{ $rest->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_full_year" id="is_full_year"
                                        value="1">
                                    <label class="form-check-label" for="is_full_year">Set for Full Year (Jan - Dec)</label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Month</label>
                                    <select name="month" id="month_select" class="form-select">
                                        @foreach (range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Year</label>
                                    <select name="year" class="form-select" required>
                                        @foreach (range(date('Y') - 1, date('Y') + 1) as $y)
                                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>
                                                {{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label">Target Amount (IDR)</label>
                            <input type="text" name="amount" class="form-control rupiah" placeholder="0" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Save Target</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- LIST DATA (KANAN) --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Target List ({{ $year }})</h5>
                    {{-- Filter Tahun --}}
                    <form action="{{ route('revenue-targets.index') }}" method="GET" class="d-flex align-items-center">
                        <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach (range(date('Y') - 1, date('Y') + 1) as $y)
                                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>
                                    {{ $y }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Restaurant</th>
                                    <th>Month</th>
                                    <th class="text-end">Amount</th>
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($targets as $target)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ $target->restaurant->name }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light-primary text-primary">
                                                {{ DateTime::createFromFormat('!m', $target->month)->format('F') }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold">
                                            Rp {{ number_format($target->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="small text-muted">{{ $target->updated_at->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            No targets set for {{ $year }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{-- withQueryString() PENTING agar filter tahun tidak hilang saat pindah halaman --}}
                        {{ $targets->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script Rupiah (Wajib Ada) --}}
    <script>
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('rupiah')) {
                let value = e.target.value;
                let numberString = value.replace(/[^,\d]/g, '').toString();
                let split = numberString.split(',');
                let sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }
                rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                e.target.value = rupiah;
            }
        });

        const checkFullYear = document.getElementById('is_full_year');
        const selectMonth = document.getElementById('month_select');

        checkFullYear.addEventListener('change', function() {
            if (this.checked) {
                selectMonth.disabled = true; // Matikan dropdown bulan
            } else {
                selectMonth.disabled = false; // Hidupkan kembali
            }
        });
    </script>
@endsection
