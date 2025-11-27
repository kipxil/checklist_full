@extends('layouts.mantis')

@section('header')
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Dashboard Overview</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item" aria-current="page">Dashboard</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">

        <div class="col-md-12">
            <div class="card bg-light-primary border-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="text-primary mb-2">
                                <i class="ti ti-chart-pie me-2"></i> Monthly Performance ({{ now()->format('F Y') }})
                            </h5>
                            <div class="d-flex align-items-baseline gap-2 mb-2">
                                <h2 class="mb-0 fw-bold">Rp {{ number_format($mtdRevenue, 0, ',', '.') }}</h2>
                                <span class="text-muted">/ Target: Rp
                                    {{ number_format($monthlyTarget, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <h2
                                class="mb-0 {{ $achievementPercent >= 100 ? 'text-success' : ($achievementPercent >= 80 ? 'text-warning' : 'text-danger') }}">
                                {{ number_format($achievementPercent, 1) }}%
                            </h2>
                            <span class="small text-muted">Achievement</span>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="progress mt-3" style="height: 20px;">
                        <div class="progress-bar {{ $achievementPercent >= 100 ? 'bg-success' : ($achievementPercent >= 80 ? 'bg-warning' : 'bg-danger') }} progress-bar-striped progress-bar-animated"
                            role="progressbar" style="width: {{ min($achievementPercent, 100) }}%"
                            aria-valuenow="{{ $achievementPercent }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- WIDGET 1: WAITING APPROVAL --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-warning text-warning">
                                {{-- Menggunakan icon Jam standar --}}
                                <i class="ti ti-clock f-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Waiting Approval</h6>
                            <div class="d-flex align-items-end mt-1">
                                <h4 class="mb-0">{{ $waitingApproval }}</h4>
                                <span
                                    class="badge bg-light-warning text-warning border border-warning ms-2">Submitted</span>
                            </div>
                            <p class="text-muted mt-2 mb-0 small">Reports waiting for manager action</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- WIDGET 2: TODAY'S REVENUE --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-primary text-primary">
                                <i class="ti ti-wallet f-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Today's Revenue</h6>
                            <div class="d-flex align-items-end mt-1">
                                <h4 class="mb-0">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</h4>
                            </div>
                            <p class="text-muted mt-2 mb-0 small">Total accumulated revenue today</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- WIDGET 3: MY DRAFTS --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-secondary text-secondary">
                                {{-- Menggunakan icon Edit standar --}}
                                <i class="ti ti-edit f-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Drafts in Progress</h6>
                            <div class="d-flex align-items-end mt-1">
                                <h4 class="mb-0">{{ $myDrafts }}</h4>
                                <span
                                    class="badge bg-light-secondary text-secondary border border-secondary ms-2">Draft</span>
                            </div>
                            <p class="text-muted mt-2 mb-0 small">Unfinished reports (You/Team)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CHART SECTION --}}
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Revenue Analytics (Last 7 Days)</h5>
                </div>
                <div class="card-body">
                    <div id="revenue-chart"></div>
                </div>
            </div>
        </div>

        {{-- COMPETITOR CHART (BARU) --}}
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Competitor Cover Comparison (Last 7 Days)</h5>
                </div>
                <div class="card-body">
                    <div id="competitor-chart"></div>
                </div>
            </div>
        </div>

        {{-- MAIN CONTENT: RECENT REPORTS TABLE --}}
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Recent Daily Reports</h5>
                    <a href="{{ route('daily-reports.index') }}" class="link-primary small fw-bold">View All Reports</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Restaurant</th>
                                    <th>Created By</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentReports as $report)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold">{{ $report->date->format('d M Y') }}</div>
                                            <div class="small text-muted">{{ $report->date->format('H:i') }}</div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark">{{ $report->restaurant->name }}</span>
                                            <div class="small text-muted">{{ $report->restaurant->code }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset('template/dist') }}/assets/images/user/avatar-2.jpg"
                                                    alt="user" class="wid-30 rounded-circle me-2">
                                                <span>{{ $report->user->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($report->status == 'approved')
                                                <span class="badge bg-light-success text-success">Approved</span>
                                            @elseif($report->status == 'submitted')
                                                <span class="badge bg-light-primary text-primary">Submitted</span>
                                            @else
                                                <span class="badge bg-light-warning text-warning">Draft</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('daily-reports.show', $report->id) }}"
                                                class="btn btn-sm btn-icon btn-link-secondary">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="ti ti-folder-off fs-1 d-block mb-2"></i>
                                            No reports available yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var options = {
                series: [{
                    name: 'Total Revenue',
                    data: @json($chartValues) // Data dari Controller
                }],
                chart: {
                    height: 350,
                    type: 'bar', // Bisa ganti 'area' atau 'line' jika mau
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '45%',
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 0
                },
                xaxis: {
                    categories: @json($chartLabels), // Label Tanggal dari Controller
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function(value) {
                            // Formatter Rupiah Sederhana (Ribuan K)
                            return "Rp " + (value / 1000).toLocaleString() + "k";
                        }
                    }
                },
                fill: {
                    opacity: 1,
                    colors: ['#4680ff'] // Warna Biru Mantis
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            // Formatter Rupiah Lengkap di Tooltip
                            return "Rp " + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        }
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#revenue-chart"), options);
            chart.render();

            // --- 2. CHART COMPETITOR (KODE BARU) ---
            var compOptions = {
                series: @json($compSeries), // Data dari Controller
                chart: {
                    height: 350,
                    type: 'line', // Line chart cocok untuk perbandingan tren
                    zoom: {
                        enabled: false
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: [3, 2, 2, 2], // Garis kita tebal, kompetitor tipis
                    curve: 'smooth',
                    dashArray: [0, 5, 5, 5] // Garis kompetitor putus-putus (opsional, biar kita menonjol)
                },
                colors: ['#4680ff', '#ff5252', '#ffba57', '#2ca87f'], // Biru(Kita), Merah, Kuning, Hijau
                xaxis: {
                    categories: @json($chartLabels), // Label tanggal sama dengan revenue
                },
                yaxis: {
                    title: {
                        text: 'Total Covers (Pax)'
                    }
                },
                legend: {
                    position: 'top'
                },
                markers: {
                    size: 4,
                    hover: {
                        size: 6
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val + " Pax"
                        }
                    }
                }
            };

            var chartComp = new ApexCharts(document.querySelector("#competitor-chart"), compOptions);
            chartComp.render();
        });
    </script>
@endsection
