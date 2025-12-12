{{-- HEADER MODAL --}}
<div class="modal-header">
    <h5 class="modal-title" id="analyticsModalLabel">
        Analytics Report: <span class="text-primary fw-bold">{{ $restaurant->name }}</span>
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

{{-- BODY MODAL --}}
<div class="modal-body">

    {{-- 1. FILTER SECTION --}}
    <div class="card bg-light border-0 mb-4">
        <div class="card-body py-3">
            <form id="analytics-filter-form" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="small text-muted fw-bold">Start Date</label>
                    <input type="date" id="filter-start-date" class="form-control form-control-sm"
                        value="{{ $startDate }}">
                </div>
                <div class="col-md-4">
                    <label class="small text-muted fw-bold">End Date</label>
                    <input type="date" id="filter-end-date" class="form-control form-control-sm"
                        value="{{ $endDate }}">
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-sm btn-primary w-100"
                        onclick="loadAnalyticsData({{ $restaurant->id }})">
                        <i class="ti ti-filter me-1"></i> Update Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- NAV TABS (Agar tidak terlalu panjang ke bawah) --}}
    <ul class="nav nav-tabs mb-3" id="analyticsTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-cover" type="button">1. Cover
                Report</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-revenue" type="button">2. Revenue
                Report</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-competitor" type="button">3.
                Competitor</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-daytrend" type="button">4. Cover by
                Day</button>
        </li>
    </ul>

    <div class="tab-content" id="analyticsTabContent">

        {{-- TAB 1: COVER REPORT --}}
        <div class="tab-pane fade show active" id="tab-cover">
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover text-center align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start">Cover Item</th>
                            @foreach ($sessions as $sess)
                                <th class="text-capitalize">{{ $sess }}</th>
                            @endforeach
                            <th class="bg-light-primary text-primary">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $colTotals = array_fill_keys($sessions, 0);
                            $grandTotal = 0;
                        @endphp

                        @forelse($coverMatrix as $item => $data)
                            <tr>
                                <td class="text-start fw-bold text-muted">{{ $item }}</td>
                                @php $rowTotal = 0; @endphp
                                @foreach ($sessions as $sess)
                                    @php
                                        $val = $data[$sess];
                                        $rowTotal += $val;
                                        $colTotals[$sess] += $val;
                                    @endphp
                                    <td>{{ $val > 0 ? number_format($val) : '-' }}</td>
                                @endforeach
                                @php $grandTotal += $rowTotal; @endphp
                                <td class="fw-bold bg-light-primary text-primary">{{ number_format($rowTotal) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($sessions) + 2 }}" class="text-muted fst-italic py-3">No cover
                                    data found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if (count($coverMatrix) > 0)
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td class="text-start">GRAND TOTAL PAX</td>
                                @foreach ($sessions as $sess)
                                    <td>{{ number_format($colTotals[$sess]) }}</td>
                                @endforeach
                                <td class="bg-primary text-white">{{ number_format($grandTotal) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
            <div class="card mt-3 border-0 bg-light">
                <div class="card-body">
                    <div id="coverReportChart"></div>
                </div>
            </div>
            <textarea id="chart-categories-data" style="display:none;">{{ json_encode($chartCategories) }}</textarea>
            <textarea id="chart-series-data" style="display:none;">{{ json_encode($chartSeries) }}</textarea>
        </div>

        {{-- TAB 2: REVENUE REPORT --}}
        <div class="tab-pane fade" id="tab-revenue">
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover text-center align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start">Revenue Item</th>
                            @foreach ($sessions as $sess)
                                <th class="text-capitalize">{{ $sess }}</th>
                            @endforeach
                            <th class="bg-light-success text-success">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $revColTotals = array_fill_keys($sessions, 0);
                            $revGrandTotal = 0;
                        @endphp

                        @foreach ($revenueMatrix as $item => $data)
                            <tr>
                                <td class="text-start fw-bold text-muted">{{ $item }}</td>
                                @php $rowTotal = 0; @endphp
                                @foreach ($sessions as $sess)
                                    @php
                                        $val = $data[$sess];
                                        $rowTotal += $val;
                                        $revColTotals[$sess] += $val;
                                    @endphp
                                    {{-- Tampilkan angka, gunakan class text-muted jika 0 --}}
                                    <td class="{{ $val == 0 ? 'text-muted text-opacity-25' : '' }}">
                                        <small>Rp</small> {{ number_format($val, 0, ',', '.') }}
                                    </td>
                                @endforeach
                                @php $revGrandTotal += $rowTotal; @endphp
                                <td class="fw-bold bg-light-success text-success">
                                    <small>Rp</small> {{ number_format($rowTotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td class="text-start">TOTAL REVENUE</td>
                            @foreach ($sessions as $sess)
                                <td><small>Rp</small> {{ number_format($revColTotals[$sess], 0, ',', '.') }}</td>
                            @endforeach
                            <td class="bg-success text-white"><small>Rp</small>
                                {{ number_format($revGrandTotal, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            {{-- A. WADAH GRAFIK REVENUE --}}
            <div class="card mt-3 border-0 bg-light">
                <div class="card-body">
                    <div id="revenueReportChart"></div>
                </div>
            </div>

            {{-- B. DATA PAYLOAD REVENUE (Hidden) --}}
            <textarea id="chart-rev-categories-data" style="display:none;">{{ json_encode($revChartCategories) }}</textarea>
            <textarea id="chart-rev-series-data" style="display:none;">{{ json_encode($revChartSeries) }}</textarea>
        </div>

        {{-- TAB 3: COMPETITOR --}}
        <div class="tab-pane fade" id="tab-competitor">
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover text-center align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start">Hotel / Venue</th>
                            @foreach ($sessions as $sess)
                                <th class="text-capitalize">{{ $sess }}</th>
                            @endforeach
                            <th class="bg-light-dark text-dark">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($competitorMatrix as $item => $data)
                            {{-- Highlight baris 'Us' --}}
                            <tr class="{{ Str::startsWith($item, 'Us') ? 'table-info' : '' }}">
                                <td class="text-start fw-bold text-muted">{{ $item }}</td>
                                @php $rowTotal = 0; @endphp
                                @foreach ($sessions as $sess)
                                    @php
                                        $val = $data[$sess];
                                        $rowTotal += $val;
                                    @endphp
                                    <td>{{ $val > 0 ? number_format($val) : '-' }}</td>
                                @endforeach
                                <td class="fw-bold">{{ number_format($rowTotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- A. WADAH GRAFIK COMPETITOR --}}
            <div class="card mt-3 border-0 bg-light">
                <div class="card-body">
                    <div id="competitorReportChart"></div>
                </div>
            </div>

            {{-- B. DATA PAYLOAD (Hidden) --}}
            <textarea id="chart-comp-categories-data" style="display:none;">{{ json_encode($compChartCategories) }}</textarea>
            <textarea id="chart-comp-series-data" style="display:none;">{{ json_encode($compChartSeries) }}</textarea>
            <div class="mt-3 small text-muted">
                <i class="ti ti-info-circle me-1"></i> Data based on accumulated daily reports within the selected
                period.
            </div>
        </div>

        {{-- TAB 4: WEEKLY TREND --}}
        <div class="tab-pane fade" id="tab-daytrend">
            {{-- C. TABEL (Sesi di Baris, Hari di Kolom) --}}
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover text-center align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start">Session Type</th>
                            @foreach ($daysOfWeek as $day)
                                <th>{{ $day }}</th>
                            @endforeach
                            <th class="bg-light-primary text-primary">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $dayColTotals = array_fill_keys($daysOfWeek, 0);
                            $dayGrandTotal = 0;
                        @endphp

                        @foreach ($sessions as $sess)
                            <tr>
                                <td class="text-start fw-bold text-muted text-capitalize">{{ $sess }}</td>
                                @php $rowTotal = 0; @endphp

                                @foreach ($daysOfWeek as $day)
                                    @php
                                        $val = $dayTrendMatrix[$sess][$day];
                                        $rowTotal += $val;
                                        $dayColTotals[$day] += $val;
                                    @endphp
                                    <td>{{ $val > 0 ? number_format($val) : '-' }}</td>
                                @endforeach

                                @php $dayGrandTotal += $rowTotal; @endphp
                                <td class="fw-bold bg-light-primary text-primary">{{ number_format($rowTotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td class="text-start">TOTAL PAX</td>
                            @foreach ($daysOfWeek as $day)
                                <td>{{ number_format($dayColTotals[$day]) }}</td>
                            @endforeach
                            <td class="bg-primary text-white">{{ number_format($dayGrandTotal) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            {{-- A. WADAH GRAFIK --}}
            <div class="card mb-3 border-0 bg-light">
                <div class="card-body">
                    <div id="dayTrendChart"></div>
                </div>
            </div>

            {{-- B. DATA PAYLOAD (Hidden) --}}
            <textarea id="chart-day-categories-data" style="display:none;">{{ json_encode($daysOfWeek) }}</textarea>
            <textarea id="chart-day-series-data" style="display:none;">{{ json_encode($dayChartSeries) }}</textarea>
            <div class="mt-3 small text-muted">
                <i class="ti ti-info-circle me-1"></i> Data shows accumulated pax count per day of the week.
            </div>
        </div>

    </div>
</div>

{{-- FOOTER MODAL --}}
<div class="modal-footer bg-light">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>
