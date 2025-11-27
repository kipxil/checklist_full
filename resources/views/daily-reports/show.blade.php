@extends('layouts.mantis')

@section('header')
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Report Detail</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('daily-reports.index') }}">Reports</a></li>
                        <li class="breadcrumb-item" aria-current="page">Detail</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        {{-- KOLOM KIRI: HEADER LAPORAN --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Report Info</h5>
                    {{-- TOMBOL BACK --}}
                    <a href="{{ route('daily-reports.index') }}" class="btn btn-sm btn-light-secondary">
                        <i class="ti ti-arrow-left"></i> Back to List
                    </a>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0">
                            <span class="text-muted mb-1 d-block">Restaurant</span>
                            <h6 class="mb-0">{{ $dailyReport->restaurant->name }} ({{ $dailyReport->restaurant->code }})
                            </h6>
                        </li>
                        <li class="list-group-item px-0">
                            <span class="text-muted mb-1 d-block">Date & Time</span>
                            {{-- TAMBAHKAN JAM DI SINI --}}
                            <h6 class="mb-0">{{ $dailyReport->date->format('d F Y - H:i') }}</h6>
                        </li>
                        <li class="list-group-item px-0">
                            <span class="text-muted mb-1 d-block">Created By</span>
                            <div class="d-flex align-items-center">
                                <img src="{{ asset('template/dist') }}/assets/images/user/avatar-2.jpg" alt="user"
                                    class="wid-30 rounded-circle me-2">
                                <h6 class="mb-0">{{ $dailyReport->user->name }}</h6>
                            </div>
                        </li>
                        @if ($dailyReport->status == 'approved')
                            <li class="list-group-item px-0 pb-3 pt-3">
                                <div class="d-grid">
                                    <a href="{{ route('daily-reports.pdf', $dailyReport->id) }}" class="btn btn-danger"
                                        target="_blank">
                                        <i class="ti ti-file-type-pdf me-1"></i> Download Report PDF
                                    </a>
                                </div>
                            </li>
                        @endif
                        <li class="list-group-item px-0">
                            <span class="text-muted mb-1 d-block">Status</span>
                            @if ($dailyReport->status == 'approved')
                                <span class="badge bg-success">Approved</span>
                                <div class="small mt-1 text-muted">by {{ $dailyReport->approver->name ?? '-' }}</div>
                            @elseif($dailyReport->status == 'submitted')
                                <span class="badge bg-primary">Submitted</span>
                            @else
                                <span class="badge bg-warning text-dark">Draft</span>
                            @endif
                        </li>
                    </ul>

                    {{-- Tombol Approve (Hanya muncul jika status submitted) --}}
                    {{-- @if ($dailyReport->status == 'submitted')
                        <div class="d-grid mt-3">
                            <form action="{{ route('daily-reports.approve', $dailyReport->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success w-100"
                                    onclick="return confirm('Apakah Anda yakin ingin menyetujui laporan ini? Data tidak bisa diubah lagi setelah ini.')">
                                    <i class="ti ti-check"></i> Approve Report
                                </button>
                            </form>
                        </div>
                    @endif --}}
                    @if ($dailyReport->status == 'submitted')
                        @hasanyrole('Super Admin|Restaurant Manager')
                            <div class="d-grid mt-3">
                                {{-- FORM APPROVE --}}
                                <form action="{{ route('daily-reports.approve', $dailyReport->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success w-100"
                                        onclick="return confirm('Setujui laporan ini?')">
                                        <i class="ti ti-check"></i> Approve Report
                                    </button>
                                </form>

                                {{-- TAMBAHKAN FORM REJECT DI BAWAH SINI --}}
                                <form action="{{ route('daily-reports.reject', $dailyReport->id) }}" method="POST"
                                    class="mt-2">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-danger w-100"
                                        onclick="return confirm('Tolak laporan ini? Status akan kembali menjadi Draft.')">
                                        <i class="ti ti-x"></i> Reject (Return to Draft)
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="alert alert-info mt-3 small">
                                <i class="ti ti-info-circle"></i> Waiting for Restaurant Manager Approval.
                            </div>
                        @endhasanyrole
                    @endif
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: DETAIL SESI (Looping) --}}
        <div class="col-md-8">
            @foreach ($dailyReport->details as $detail)
                <div class="card mb-3">
                    <div
                        class="card-header d-flex justify-content-between align-items-center
                    {{ $detail->session_type == 'breakfast'
                        ? 'bg-light-warning'
                        : ($detail->session_type == 'lunch'
                            ? 'bg-light-primary'
                            : 'bg-light-danger') }}">
                        <h5 class="mb-0 text-capitalize">
                            @if ($detail->session_type == 'breakfast')
                                <i class="ti ti-sun me-2"></i>
                            @elseif($detail->session_type == 'lunch')
                                <i class="ti ti-soup me-2"></i>
                            @else
                                <i class="ti ti-moon-stars me-2"></i>
                            @endif
                            {{ $detail->session_type }} Report
                        </h5>
                        @if ($detail->thematic)
                            <span class="badge bg-dark">{{ $detail->thematic }}</span>
                        @endif
                    </div>
                    <div class="card-body">

                        {{-- 1. REVENUE TABLE --}}
                        <h6 class="text-muted text-uppercase small fw-bold">Revenue Summary</h6>
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Food</th>
                                        <th>Beverage</th>
                                        <th>Others</th>
                                        <th>Event</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Rp {{ number_format($detail->revenue_food) }}</td>
                                        <td>Rp {{ number_format($detail->revenue_beverage) }}</td>
                                        <td>Rp {{ number_format($detail->revenue_others) }}</td>
                                        <td>Rp {{ number_format($detail->revenue_event) }}</td>
                                        <td class="text-end fw-bold table-active">
                                            Rp
                                            {{ number_format($detail->revenue_food + $detail->revenue_beverage + $detail->revenue_others + $detail->revenue_event) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- 2. COVER DATA (JSON DUMP YANG RAPI) --}}
                        <h6 class="text-muted text-uppercase small fw-bold mt-4">Cover Report Details</h6>
                        @if (!empty($detail->cover_data))
                            <div class="row g-2">
                                @foreach ($detail->cover_data as $key => $value)
                                    <div class="col-md-4 col-6">
                                        <div class="p-2 border rounded bg-light">
                                            <small class="d-block text-muted text-uppercase" style="font-size: 10px;">
                                                {{ str_replace('_', ' ', $key) }}
                                            </small>
                                            <span class="fw-bold">{{ $value }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted small">No cover data recorded.</p>
                        @endif

                        {{-- 4. UPSELLING & COMPETITOR (Accordion agar tidak penuh) --}}
                        {{-- <div class="accordion mt-3" id="accordion-{{ $detail->id }}">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-{{ $detail->id }}">
                                    <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse-{{ $detail->id }}">
                                        View Upselling & Competitor Data
                                    </button>
                                </h2>
                                <div id="collapse-{{ $detail->id }}" class="accordion-collapse collapse"
                                    data-bs-parent="#accordion-{{ $detail->id }}">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Upselling Data:</strong>
                                                <pre class="bg-light p-2 rounded mt-1" style="font-size: 11px;">{{ json_encode($detail->upselling_data, JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Competitor Data:</strong>
                                                <pre class="bg-light p-2 rounded mt-1" style="font-size: 11px;">{{ json_encode($detail->competitor_data, JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                        <hr class="my-4">

                        {{-- A. UPSELLING SECTION --}}
                        <h6 class="text-muted text-uppercase small fw-bold mb-3">
                            <i class="ti ti-trending-up me-1"></i> Upselling Performance
                        </h6>

                        <div class="row">
                            {{-- Food Upselling --}}
                            <div class="col-md-6">
                                <div class="card bg-light-primary border-0">
                                    <div class="card-body p-3">
                                        <h6 class="card-title text-primary mb-2"><i class="ti ti-tools-kitchen-2"></i> Food
                                            Items</h6>
                                        {{-- LOGIKA FIX: Decode dulu jika datanya String --}}
                                        @php
                                            $foodItems = $detail->upselling_data['food'] ?? [];
                                            if (is_string($foodItems)) {
                                                $foodItems = json_decode($foodItems, true) ?? [];
                                            }
                                        @endphp

                                        @if (!empty($foodItems) && is_array($foodItems) && count($foodItems) > 0)
                                            <div class="table-responsive">
                                                <table class="table table-sm table-borderless mb-0 small">
                                                    <thead class="text-muted">
                                                        <tr>
                                                            <th>Menu Name</th>
                                                            <th class="text-end">Qty</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($foodItems as $item)
                                                            <tr class="border-bottom border-light">
                                                                <td class="fw-bold text-dark">{{ $item['name'] ?? '-' }}
                                                                </td>
                                                                <td class="text-end">
                                                                    <span
                                                                        class="badge bg-white text-primary border border-primary">
                                                                        {{ $item['pax'] ?? 0 }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <span class="small text-muted fst-italic">- No food upselling -</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Beverage Upselling --}}
                            <div class="col-md-6">
                                <div class="card bg-light-success border-0">
                                    <div class="card-body p-3">
                                        <h6 class="card-title text-success mb-2"><i class="ti ti-glass-full"></i> Beverage
                                            Items</h6>
                                        @php
                                            $bevItems = $detail->upselling_data['beverage'] ?? [];
                                            if (is_string($bevItems)) {
                                                $bevItems = json_decode($bevItems, true) ?? [];
                                            }
                                        @endphp

                                        @if (!empty($bevItems) && is_array($bevItems) && count($bevItems) > 0)
                                            <div class="table-responsive">
                                                <table class="table table-sm table-borderless mb-0 small">
                                                    <thead class="text-muted">
                                                        <tr>
                                                            <th>Menu Name</th>
                                                            <th class="text-end">Qty</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($bevItems as $item)
                                                            <tr class="border-bottom border-light">
                                                                <td class="fw-bold text-dark">{{ $item['name'] ?? '-' }}
                                                                </td>
                                                                <td class="text-end">
                                                                    <span
                                                                        class="badge bg-white text-success border border-success">
                                                                        {{ $item['pax'] ?? 0 }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <span class="small text-muted fst-italic">- No beverage upselling -</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- B. STAFF & VIP SECTION --}}
                        <div class="row mt-4">

                            {{-- Staff On Duty (Badges) --}}
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small fw-bold mb-2">
                                    <i class="ti ti-users me-1"></i> Staff On Duty
                                </h6>
                                <div class="d-flex flex-wrap gap-2">
                                    {{-- LOGIKA ANTI-ERROR: Cek tipe data dulu --}}
                                    @php
                                        $staffList = $detail->staff_on_duty;
                                        if (is_string($staffList)) {
                                            $staffList = json_decode($staffList, true) ?? []; // Paksa decode
                                        }
                                    @endphp

                                    @if (!empty($staffList) && is_array($staffList))
                                        @foreach ($staffList as $staff)
                                            <span class="badge bg-light-secondary text-dark border">
                                                <i class="ti ti-user me-1"></i> {{ $staff }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="small text-muted">- No staff recorded -</span>
                                    @endif
                                </div>
                            </div>

                            {{-- VIP List (List Group) --}}
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small fw-bold mb-2">
                                    <i class="ti ti-crown me-1 text-warning"></i> VIP Guests
                                </h6>

                                {{-- LOGIKA ANTI-ERROR: Cek tipe data dulu --}}
                                @php
                                    $vipList = $detail->vip_remarks;
                                    if (is_string($vipList)) {
                                        $vipList = json_decode($vipList, true) ?? []; // Paksa decode
                                    }
                                @endphp

                                @if (!empty($vipList) && is_array($vipList))
                                    <ul class="list-group list-group-flush border rounded-2 overflow-hidden">
                                        @foreach ($vipList as $vip)
                                            <li
                                                class="list-group-item d-flex justify-content-between align-items-center bg-light px-3 py-2">
                                                <div>
                                                    {{-- Gunakan null coalescing (??) jaga-jaga key tidak ada --}}
                                                    <span
                                                        class="d-block fw-bold text-dark small">{{ $vip['name'] ?? '-' }}</span>
                                                    <span class="d-block text-muted"
                                                        style="font-size: 10px;">{{ $vip['position'] ?? '-' }}</span>
                                                </div>
                                                <i class="ti ti-star-filled text-warning fs-5"></i>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="small text-muted fst-italic">- No VIP guests -</span>
                                @endif
                            </div>
                        </div>

                        {{-- C. COMPETITOR & REMARKS --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0 small fw-bold">Competitor & General Remarks</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row align-items-start">
                                            {{-- Competitor --}}
                                            <div class="col-md-6 border-end">
                                                <small class="text-muted d-block mb-2">Competitor Cover Comparison</small>
                                                <div class="d-flex gap-3 text-center">
                                                    @if (!empty($detail->competitor_data))
                                                        @foreach ($detail->competitor_data as $key => $val)
                                                            <div>
                                                                <h4 class="mb-0 fw-bold">{{ $val }}</h4>
                                                                <span class="text-muted"
                                                                    style="font-size: 10px; text-transform: uppercase;">
                                                                    {{ str_replace(['_cover', 'cover'], '', $key) }}
                                                                </span>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <span class="small text-muted">- No data -</span>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- General Remarks --}}
                                            <div class="col-md-6 ps-md-4 pt-3 pt-md-0">
                                                <small class="text-muted d-block mb-1">General Notes</small>
                                                <p class="mb-0 small text-dark">
                                                    {{ $detail->remarks ?? '-' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
