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

        {{-- PERFORMANCE SECTION (TABS VIEW) --}}
        <div class="col-md-12">
            <div class="card">
                <div class="card-header p-0 mx-3 mt-3 border-0">
                    <ul class="nav nav-tabs profile-tabs" id="myTab" role="tablist">
                        {{-- Tab 1: Overview --}}
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active pb-2" id="overview-tab" data-bs-toggle="tab" href="#overview"
                                role="tab" aria-selected="true">
                                <i class="ti ti-chart-pie me-2"></i> Overview
                            </a>
                        </li>

                        {{-- Tab 2: Outlet Details (Hanya muncul jika Multi-Resto) --}}
                        @if (Auth::user()->hasRole('Super Admin') || Auth::user()->restaurants->count() > 1)
                            <li class="nav-item" role="presentation">
                                <a class="nav-link pb-2" id="breakdown-tab" data-bs-toggle="tab" href="#breakdown"
                                    role="tab" aria-selected="false">
                                    <i class="ti ti-list-details me-2"></i> Outlet Details
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="myTabContent">

                        {{-- KONTEN TAB 1: OVERVIEW --}}
                        <div class="tab-pane fade show active" id="overview" role="tabpanel"
                            aria-labelledby="overview-tab">
                            <div class="row align-items-center bg-light-primary rounded p-4 border border-primary-subtle">
                                <div class="col-md-8">
                                    <h5 class="text-primary mb-2">
                                        Monthly Performance ({{ now()->format('F Y') }})
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
                                <div class="col-12 mt-3">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar {{ $achievementPercent >= 100 ? 'bg-success' : ($achievementPercent >= 80 ? 'bg-warning' : 'bg-danger') }} progress-bar-striped progress-bar-animated"
                                            role="progressbar" style="width: {{ min($achievementPercent, 100) }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- KONTEN TAB 2: BREAKDOWN LIST --}}
                        @if (Auth::user()->hasRole('Super Admin') || Auth::user()->restaurants->count() > 1)
                            <div class="tab-pane fade" id="breakdown" role="tabpanel" aria-labelledby="breakdown-tab">
                                <div style="max-height: 400px; overflow-y: auto;">
                                    <ul class="list-group list-group-flush">
                                        @foreach ($breakdownPerformance as $data)
                                            <li class="list-group-item px-0 py-3 border-bottom">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold">
                                                            {{-- Panggil fungsi JS openAnalyticsModal dengan ID Resto --}}
                                                            <a href="javascript:void(0)"
                                                                onclick="openAnalyticsModal({{ $data['id'] }})"
                                                                class="text-decoration-none text-primary">
                                                                {{ $data['name'] }} <i
                                                                    class="ti ti-external-link ms-1 small"></i>
                                                            </a>
                                                        </h6>
                                                        <small class="text-muted">
                                                            <span class="text-dark fw-bold">Rp
                                                                {{ number_format($data['actual'], 0, ',', '.') }}</span>
                                                            / Target: Rp {{ number_format($data['target'], 0, ',', '.') }}
                                                        </small>
                                                    </div>
                                                    <div class="text-end">
                                                        <span
                                                            class="badge {{ $data['percentage'] >= 100 ? 'bg-light-success text-success' : ($data['percentage'] >= 80 ? 'bg-light-warning text-warning' : 'bg-light-danger text-danger') }} f-12">
                                                            {{ number_format($data['percentage'], 1) }}%
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar {{ $data['percentage'] >= 100 ? 'bg-success' : ($data['percentage'] >= 80 ? 'bg-warning' : 'bg-danger') }}"
                                                        role="progressbar"
                                                        style="width: {{ min($data['percentage'], 100) }}%">
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

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

        <div class="modal fade" id="analyticsModal" tabindex="-1" aria-labelledby="analyticsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                {{-- Konten ini akan diganti oleh AJAX (Loading Spinner Default) --}}
                <div class="modal-content" id="analyticsModalContent">
                    <div class="modal-body text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading analytics data...</p>
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
        // 1. Fungsi Buka Modal Pertama Kali
        function openAnalyticsModal(restaurantId) {
            // Tampilkan Modal Bootstrap
            var myModal = new bootstrap.Modal(document.getElementById('analyticsModal'));
            myModal.show();

            // Panggil Data
            loadAnalyticsData(restaurantId);
        }

        // 2. Fungsi Load Data (AJAX) - Dipakai saat buka modal ATAU saat filter tanggal
        function loadAnalyticsData(restaurantId) {
            // Ambil elemen konten modal
            const contentDiv = document.getElementById('analyticsModalContent');

            // Cek apakah user sedang filter tanggal (elemen input ada di dalam modal)
            const startDateInput = document.getElementById('filter-start-date');
            const endDateInput = document.getElementById('filter-end-date');

            let url = `{{ url('/dashboard/analytics') }}/${restaurantId}`;

            // Jika input tanggal ada (artinya ini reload filter), tambahkan parameter
            if (startDateInput && endDateInput) {
                url += `?start_date=${startDateInput.value}&end_date=${endDateInput.value}`;

                // Tampilkan loading overlay tipis biar UX bagus
                contentDiv.style.opacity = '0.5';
                contentDiv.style.pointerEvents = 'none';
            } else {
                // Tampilkan Full Spinner (Reset tampilan awal)
                contentDiv.innerHTML = `
                <div class="modal-body text-center p-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3 text-muted">Retrieving data...</p>
                </div>
            `;
                contentDiv.style.opacity = '1';
            }

            // Fetch Data dari Server
            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.text();
                })
                .then(html => {
                    // Masukkan HTML Partial View ke dalam Modal
                    contentDiv.innerHTML = html;
                    contentDiv.style.opacity = '1';
                    contentDiv.style.pointerEvents = 'auto';
                })
                .catch(error => {
                    contentDiv.innerHTML = `
                    <div class="modal-header"><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body text-center text-danger p-5">
                        <i class="ti ti-alert-triangle fs-1 mb-3"></i>
                        <p>Failed to load data. Please try again.</p>
                    </div>
                `;
                });
        }
        // Variabel Global untuk menyimpan object Chart
        let coverChartInstance = null;
        let revenueChartInstance = null;
        let competitorChartInstance = null;
        let dayTrendChartInstance = null;

        function openAnalyticsModal(restaurantId) {
            var myModal = new bootstrap.Modal(document.getElementById('analyticsModal'));
            myModal.show();
            loadAnalyticsData(restaurantId);
        }

        function loadAnalyticsData(restaurantId) {
            const contentDiv = document.getElementById('analyticsModalContent');

            // ... (Kode Ambil Input Filter Tanggal - SAMA SEPERTI SEBELUMNYA) ...
            const startDateInput = document.getElementById('filter-start-date');
            const endDateInput = document.getElementById('filter-end-date');
            let url = `{{ url('/dashboard/analytics') }}/${restaurantId}`;

            if (startDateInput && endDateInput) {
                url += `?start_date=${startDateInput.value}&end_date=${endDateInput.value}`;
                contentDiv.style.opacity = '0.5';
                contentDiv.style.pointerEvents = 'none';
            } else {
                // ... (Kode Loading Spinner - SAMA SEPERTI SEBELUMNYA) ...
                contentDiv.innerHTML =
                    `<div class="modal-body text-center p-5"><div class="spinner-border text-primary"></div></div>`;
            }

            fetch(url)
                .then(response => response.text())
                .then(html => {
                    contentDiv.innerHTML = html;
                    contentDiv.style.opacity = '1';
                    contentDiv.style.pointerEvents = 'auto';

                    // === TAMBAHAN BARU: RENDER CHART SETELAH HTML MUNCUL ===
                    renderCoverChart();
                    renderRevenueChart();
                    renderCompetitorChart();
                    renderDayTrendChart();
                })
                .catch(error => {
                    console.error(error);
                    contentDiv.innerHTML = `<p class="text-center text-danger p-5">Failed to load data.</p>`;
                });
        }

        // --- FUNGSI BARU UNTUK RENDER CHART ---
        function renderCoverChart() {
            // 1. Ambil Data dari Textarea Tersembunyi
            const catElement = document.getElementById('chart-categories-data');
            const serElement = document.getElementById('chart-series-data');

            if (!catElement || !serElement) return; // Stop jika elemen tidak ada

            const categories = JSON.parse(catElement.value);
            const series = JSON.parse(serElement.value);

            // 2. Hapus Chart Lama (Jika ada) agar tidak error tumpang tindih
            if (coverChartInstance) {
                coverChartInstance.destroy();
            }

            // 3. Konfigurasi ApexCharts (Stacked Column)
            var options = {
                series: series,
                chart: {
                    type: 'bar',
                    height: 350,
                    stacked: true, // Mode Bertumpuk
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'inherit' // Ikuti font website
                },
                plotOptions: {
                    bar: {
                        horizontal: false, // Ubah true jika label item terlalu panjang
                        columnWidth: '50%',
                        borderRadius: 4
                    },
                },
                dataLabels: {
                    enabled: false // Matikan angka di dalam bar agar bersih
                },
                stroke: {
                    width: 1,
                    colors: ['#fff']
                },
                xaxis: {
                    categories: categories, // Item Cover (In House, Walk In, dll)
                },
                yaxis: {
                    title: {
                        text: 'Total Pax'
                    }
                },
                fill: {
                    opacity: 1
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left'
                },
                colors: ['#ffc107', '#0d6efd', '#212529',
                    '#6610f2'
                ] // Warna: Kuning, Biru, Hitam, Ungu (Sesuai tema sesi)
            };

            // 4. Render Chart
            coverChartInstance = new ApexCharts(document.querySelector("#coverReportChart"), options);
            coverChartInstance.render();
        }

        function renderRevenueChart() {
            // 1. Ambil Data
            const catElement = document.getElementById('chart-rev-categories-data');
            const serElement = document.getElementById('chart-rev-series-data');

            if (!catElement || !serElement) return;

            const categories = JSON.parse(catElement.value);
            const series = JSON.parse(serElement.value);

            // 2. Destroy Old Chart
            if (revenueChartInstance) {
                revenueChartInstance.destroy();
            }

            // 3. Config (Mirip Cover, tapi ada Yaxis formatter)
            var options = {
                series: series,
                chart: {
                    type: 'bar',
                    height: 350,
                    stacked: true,
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'inherit'
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '50%',
                        borderRadius: 4
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 1,
                    colors: ['#fff']
                },
                xaxis: {
                    categories: categories,
                },
                yaxis: {
                    title: {
                        text: 'Revenue (IDR)'
                    },
                    labels: {
                        // FORMATTER RUPIAH DI SUMBU Y
                        formatter: function(value) {
                            return value.toLocaleString('id-ID'); // Contoh: 1.000.000
                        }
                    }
                },
                tooltip: {
                    y: {
                        // FORMATTER RUPIAH DI TOOLTIP (Saat mouse hover)
                        formatter: function(val) {
                            return "Rp " + val.toLocaleString('id-ID');
                        }
                    }
                },
                fill: {
                    opacity: 1
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left'
                },
                colors: ['#ffc107', '#0d6efd', '#212529', '#6610f2'] // Kuning, Biru, Hitam, Ungu
            };

            // 4. Render
            revenueChartInstance = new ApexCharts(document.querySelector("#revenueReportChart"), options);
            revenueChartInstance.render();
        }

        function renderCompetitorChart() {
            // 1. Ambil Data
            const catElement = document.getElementById('chart-comp-categories-data');
            const serElement = document.getElementById('chart-comp-series-data');

            if (!catElement || !serElement) return;

            const categories = JSON.parse(catElement.value);
            const series = JSON.parse(serElement.value);

            // 2. Destroy Old Chart
            if (competitorChartInstance) {
                competitorChartInstance.destroy();
            }

            // 3. Config
            var options = {
                series: series,
                chart: {
                    type: 'bar',
                    height: 350,
                    stacked: true,
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'inherit'
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '50%',
                        borderRadius: 4
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 1,
                    colors: ['#fff']
                },
                xaxis: {
                    categories: categories, // Nama Hotel
                },
                yaxis: {
                    title: {
                        text: 'Total Pax'
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val + " Pax";
                        }
                    }
                },
                fill: {
                    opacity: 1
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left'
                },
                colors: ['#ffc107', '#0d6efd', '#212529', '#6610f2'] // Tetap konsisten (Kuning, Biru, Hitam, Ungu)
            };

            // 4. Render
            competitorChartInstance = new ApexCharts(document.querySelector("#competitorReportChart"), options);
            competitorChartInstance.render();
        }

        function renderDayTrendChart() {
            const catElement = document.getElementById('chart-day-categories-data');
            const serElement = document.getElementById('chart-day-series-data');

            if (!catElement || !serElement) return;

            const categories = JSON.parse(catElement.value);
            const series = JSON.parse(serElement.value);

            if (dayTrendChartInstance) {
                dayTrendChartInstance.destroy();
            }

            var options = {
                series: series,
                chart: {
                    type: 'line', // Ganti jadi 'line' untuk melihat tren
                    height: 350,
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'inherit'
                },
                stroke: {
                    curve: 'smooth', // Garis melengkung halus
                    width: 3
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: categories, // Mon, Tue, Wed...
                },
                yaxis: {
                    title: {
                        text: 'Accumulated Pax'
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val + " Pax";
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left'
                },
                colors: ['#ffc107', '#0d6efd', '#212529', '#6610f2'] // Kuning, Biru, Hitam, Ungu
            };

            dayTrendChartInstance = new ApexCharts(document.querySelector("#dayTrendChart"), options);
            dayTrendChartInstance.render();
        }
    </script>
@endsection
