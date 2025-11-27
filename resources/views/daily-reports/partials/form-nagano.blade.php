{{-- FORM KHUSUS NAGANO (NGN) --}}

@php
    // 1. Ambil Data Detail per Sesi (Lunch & Dinner)
    $lc = $details['lunch'] ?? null;
    $dn = $details['dinner'] ?? null;

    // 2. Ambil Data Master (Staff & Menu) khusus Nagano
    // Pastikan kode 'NGN' sesuai dengan yang ada di database Anda (ceklis apakah NGN atau NJR)
    $restoNgn = $restaurants->where('code', 'NJR')->first();

    // A. Staff List
    $myStaffList = $restoNgn ? $restoNgn->users : [];

    // B. Upselling Menu (Food & Beverage)
    $myMenu = $restoNgn && isset($upsellingItems[$restoNgn->id]) ? $upsellingItems[$restoNgn->id] : collect([]);
    $foods = $myMenu->where('type', 'food');
    $beverages = $myMenu->where('type', 'beverage');
@endphp

{{-- ============================================================ --}}
{{-- SESSION: LUNCH --}}
{{-- ============================================================ --}}
<div class="card mb-4">
    <div class="card-header bg-light-danger">
        <h5 class="mb-0 text-danger"><i class="ti ti-sun"></i> Lunch Report</h5>
    </div>
    <div class="card-body">

        {{-- 1. COVER REPORT (KOMPLEKS) --}}
        <h6 class="fw-bold text-muted mt-3">1. Cover Report Details</h6>

        {{-- Teppanyaki --}}
        <div class="p-3 border rounded mb-3 bg-white">
            <span class="badge bg-dark mb-2">TEPPANYAKI</span>
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="small text-muted">In-House (Adult)</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[lunch][cover_data][teppanyaki_inhouse]"
                        value="{{ old('session.lunch.cover_data.teppanyaki_inhouse', $lc->cover_data['teppanyaki_inhouse'] ?? '') }}"
                        placeholder="0">
                </div>
                <div class="col-md-4">
                    <label class="small text-muted">Walk-In (Adult)</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[lunch][cover_data][teppanyaki_walkin]"
                        value="{{ old('session.lunch.cover_data.teppanyaki_walkin', $lc->cover_data['teppanyaki_walkin'] ?? '') }}"
                        placeholder="0">
                </div>
                <div class="col-md-4">
                    <label class="small text-muted">Event (Adult)</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[lunch][cover_data][teppanyaki_event]"
                        value="{{ old('session.lunch.cover_data.teppanyaki_event', $lc->cover_data['teppanyaki_event'] ?? '') }}"
                        placeholder="0">
                </div>
            </div>
        </div>

        {{-- Yakiniku --}}
        <div class="p-3 border rounded mb-3 bg-white">
            <span class="badge bg-danger mb-2">YAKINIKU</span>
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="small text-muted">In-House (Adult)</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[lunch][cover_data][yakiniku_inhouse]"
                        value="{{ old('session.lunch.cover_data.yakiniku_inhouse', $lc->cover_data['yakiniku_inhouse'] ?? '') }}"
                        placeholder="0">
                </div>
                <div class="col-md-4">
                    <label class="small text-muted">Walk-In (Adult)</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[lunch][cover_data][yakiniku_walkin]"
                        value="{{ old('session.lunch.cover_data.yakiniku_walkin', $lc->cover_data['yakiniku_walkin'] ?? '') }}"
                        placeholder="0">
                </div>
                <div class="col-md-4">
                    <label class="small text-muted">Event (Adult)</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[lunch][cover_data][yakiniku_event]"
                        value="{{ old('session.lunch.cover_data.yakiniku_event', $lc->cover_data['yakiniku_event'] ?? '') }}"
                        placeholder="0">
                </div>
            </div>
        </div>

        {{-- AYCE & Child --}}
        {{-- <div class="p-3 border rounded mb-3 bg-white">
            <span class="badge bg-info mb-2">AYCE & CHILD</span>
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="small text-muted fw-bold">AYCE Total (Adult)</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[lunch][cover_data][ayce_total]"
                        value="{{ old('session.lunch.cover_data.ayce_total', $lc->cover_data['ayce_total'] ?? '') }}"
                        placeholder="0">
                </div>
                <div class="col-md-3">
                    <label class="small text-muted">Child In-House</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[lunch][cover_data][child_inhouse]"
                        value="{{ old('session.lunch.cover_data.child_inhouse', $lc->cover_data['child_inhouse'] ?? '') }}"
                        placeholder="0">
                </div>
                <div class="col-md-3">
                    <label class="small text-muted">Child Walk-In</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[lunch][cover_data][child_walkin]"
                        value="{{ old('session.lunch.cover_data.child_walkin', $lc->cover_data['child_walkin'] ?? '') }}"
                        placeholder="0">
                </div>
                <div class="col-md-3">
                    <label class="small text-muted">Child Event</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[lunch][cover_data][child_event]"
                        value="{{ old('session.lunch.cover_data.child_event', $lc->cover_data['child_event'] ?? '') }}"
                        placeholder="0">
                </div>
            </div>
        </div> --}}

        <hr>

        {{-- 2. REVENUE REPORT (Format Rupiah) --}}
        <h6 class="fw-bold text-muted mt-3">2. Revenue Report (IDR)</h6>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label small">Food Revenue</label>
                <input type="text" class="form-control rupiah" name="session[lunch][revenue_food]"
                    value="{{ old('session.lunch.revenue_food', isset($lc->revenue_food) ? number_format($lc->revenue_food, 0, ',', '.') : '') }}"
                    placeholder="0" autocomplete="off">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Beverage Revenue</label>
                <input type="text" class="form-control rupiah" name="session[lunch][revenue_beverage]"
                    value="{{ old('session.lunch.revenue_beverage', isset($lc->revenue_beverage) ? number_format($lc->revenue_beverage, 0, ',', '.') : '') }}"
                    placeholder="0" autocomplete="off">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Others Revenue</label>
                <input type="text" class="form-control rupiah" name="session[lunch][revenue_others]"
                    value="{{ old('session.lunch.revenue_others', isset($lc->revenue_others) ? number_format($lc->revenue_others, 0, ',', '.') : '') }}"
                    placeholder="0" autocomplete="off">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Event Revenue</label>
                <input type="text" class="form-control rupiah" name="session[lunch][revenue_event]"
                    value="{{ old('session.lunch.revenue_event', isset($lc->revenue_event) ? number_format($lc->revenue_event, 0, ',', '.') : '') }}"
                    placeholder="0" autocomplete="off">
            </div>
        </div>

        <hr>

        {{-- 3. UPSELLING & REMARKS (Smart Components) --}}
        <h6 class="fw-bold text-muted mt-3">3. Upselling & Remarks</h6>
        <div class="row g-3">
            {{-- Food Upselling --}}
            <div class="col-md-6">
                <label class="form-label small fw-bold">Upselling Menu (Food)</label>
                @php
                    $lcFoodVal = old('session.lunch.upselling_data.food', $lc->upselling_data['food'] ?? []);
                    $lcFoodJson = is_array($lcFoodVal) ? json_encode($lcFoodVal) : $lcFoodVal;
                @endphp
                <input type="hidden" id="input-lunch-food-NJR" name="session[lunch][upselling_data][food]"
                    value="{{ $lcFoodJson }}">

                <div class="input-group mb-2">
                    <select class="form-select form-select-sm" id="select-lunch-food-NJR">
                        <option value="" selected>Select Food...</option>
                        @foreach ($foods as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" class="form-control form-control-sm" id="pax-lunch-food-NJR"
                        placeholder="Qty" style="max-width: 70px;">
                    <button class="btn btn-sm btn-dark" type="button"
                        onclick="addUpsellingItem('lunch', 'food', 'NJR')"><i class="ti ti-plus"></i> Add</button>
                </div>
                <ul class="list-group small" id="list-lunch-food-NJR"></ul>
            </div>

            {{-- Beverage Upselling --}}
            <div class="col-md-6">
                <label class="form-label small fw-bold">Beverage Upselling</label>
                @php
                    $lcBevVal = old('session.lunch.upselling_data.beverage', $lc->upselling_data['beverage'] ?? []);
                    $lcBevJson = is_array($lcBevVal) ? json_encode($lcBevVal) : $lcBevVal;
                @endphp
                <input type="hidden" id="input-lunch-beverage-NJR" name="session[lunch][upselling_data][beverage]"
                    value="{{ $lcBevJson }}">

                <div class="input-group mb-2">
                    <select class="form-select form-select-sm" id="select-lunch-beverage-NJR">
                        <option value="" selected>Select Drink...</option>
                        @foreach ($beverages as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" class="form-control form-control-sm" id="pax-lunch-beverage-NJR"
                        placeholder="Qty" style="max-width: 70px;">
                    <button class="btn btn-sm btn-dark" type="button"
                        onclick="addUpsellingItem('lunch', 'beverage', 'NJR')"><i class="ti ti-plus"></i> Add</button>
                </div>
                <ul class="list-group small" id="list-lunch-beverage-NJR"></ul>
            </div>

            {{-- VIP List --}}
            <div class="col-md-12 mt-3">
                <label class="form-label small fw-bold">VIP 1 & 2 List</label>
                @php
                    $lcVipVal = old('session.lunch.vip_remarks', $lc->vip_remarks ?? []);
                    $lcVipJson = is_array($lcVipVal) ? json_encode($lcVipVal) : $lcVipVal;
                @endphp
                <input type="hidden" id="input-vip-lunch-NJR" name="session[lunch][vip_remarks]"
                    value="{{ $lcVipJson }}">

                <div class="input-group mb-2">
                    <input type="text" class="form-control form-control-sm" id="vip-name-lunch-NJR"
                        placeholder="Guest Name (e.g. Mr. Budi)">
                    <input type="text" class="form-control form-control-sm" id="vip-pos-lunch-NJR"
                        placeholder="Position/Title (e.g. CEO)">
                    <button class="btn btn-sm btn-dark" type="button" onclick="addVipItem('lunch', 'NJR')"><i
                            class="ti ti-plus"></i> Add</button>
                </div>
                <ul class="list-group small" id="list-vip-lunch-NJR"></ul>
            </div>

            {{-- Remarks --}}
            <div class="col-md-12">
                <label class="form-label small">General Remarks</label>
                <textarea class="form-control" name="session[lunch][remarks]">{{ old('session.lunch.remarks', $lc->remarks ?? '') }}</textarea>
            </div>

            {{-- Staff On Duty (Multi Select) --}}
            <div class="col-md-12 mt-3">
                <label class="form-label small fw-bold">Staff on Duty</label>
                @php
                    $lcStaffVal = old('session.lunch.staff_on_duty', $lc->staff_on_duty ?? []);
                    $lcStaffJson = is_array($lcStaffVal) ? json_encode($lcStaffVal) : $lcStaffVal;
                @endphp
                <input type="hidden" id="input-staff-lunch-NJR" name="session[lunch][staff_on_duty]"
                    value="{{ $lcStaffJson }}">

                <div class="input-group mb-2">
                    <select class="form-select form-select-sm" id="select-staff-lunch-NJR">
                        <option value="" selected>Select Staff...</option>
                        @foreach ($myStaffList as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-dark" type="button" onclick="addStaffItem('lunch', 'NJR')"><i
                            class="ti ti-plus"></i> Add</button>
                </div>
                <div id="list-staff-lunch-NJR" class="d-flex flex-wrap"></div>
            </div>
        </div>

        <hr>

        {{-- 4. COMPETITOR COMPARISON --}}
        <h6 class="fw-bold text-muted mt-3">4. Competitor Comparison</h6>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small">Shangri-La</label>
                <input type="number" class="form-control" name="session[lunch][competitor_data][shangrila_cover]"
                    value="{{ old('session.lunch.competitor_data.shangrila_cover', $lc->competitor_data['shangrila_cover'] ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small">JW Marriott</label>
                <input type="number" class="form-control" name="session[lunch][competitor_data][jw_marriott_cover]"
                    value="{{ old('session.lunch.competitor_data.jw_marriott_cover', $lc->competitor_data['jw_marriott_cover'] ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Sheraton</label>
                <input type="number" class="form-control" name="session[lunch][competitor_data][sheraton_cover]"
                    value="{{ old('session.lunch.competitor_data.sheraton_cover', $lc->competitor_data['sheraton_cover'] ?? '') }}">
            </div>
        </div>

    </div>
</div>


{{-- ============================================================ --}}
{{-- SESSION: DINNER --}}
{{-- ============================================================ --}}
<div class="card mb-4">
    <div class="card-header bg-light-dark text-white">
        <h5 class="mb-0"><i class="ti ti-moon"></i> Dinner Report</h5>
    </div>
    <div class="card-body">

        {{-- (STRUKTUR DINNER SAMA PERSIS DENGAN LUNCH) --}}
        {{-- Saya copykan lengkap agar Anda tinggal pakai --}}

        <h6 class="fw-bold text-muted mt-3">1. Cover Report Details</h6>

        <div class="p-3 border rounded mb-3 bg-white">
            <span class="badge bg-dark mb-2">TEPPANYAKI</span>
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="small text-muted">In-House</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[dinner][cover_data][teppanyaki_inhouse]"
                        value="{{ old('session.dinner.cover_data.teppanyaki_inhouse', $dn->cover_data['teppanyaki_inhouse'] ?? '') }}"
                        placeholder="0">
                </div>
                <div class="col-md-4">
                    <label class="small text-muted">Walk-In</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[dinner][cover_data][teppanyaki_walkin]"
                        value="{{ old('session.dinner.cover_data.teppanyaki_walkin', $dn->cover_data['teppanyaki_walkin'] ?? '') }}"
                        placeholder="0">
                </div>
                <div class="col-md-4">
                    <label class="small text-muted">Event</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[dinner][cover_data][teppanyaki_event]"
                        value="{{ old('session.dinner.cover_data.teppanyaki_event', $dn->cover_data['teppanyaki_event'] ?? '') }}"
                        placeholder="0">
                </div>
            </div>
        </div>

        <div class="p-3 border rounded mb-3 bg-white">
            <span class="badge bg-danger mb-2">YAKINIKU</span>
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="small text-muted">In-House</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[dinner][cover_data][yakiniku_inhouse]"
                        value="{{ old('session.dinner.cover_data.yakiniku_inhouse', $dn->cover_data['yakiniku_inhouse'] ?? '') }}"
                        placeholder="0">
                </div>
                <div class="col-md-4">
                    <label class="small text-muted">Walk-In</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[dinner][cover_data][yakiniku_walkin]"
                        value="{{ old('session.dinner.cover_data.yakiniku_walkin', $dn->cover_data['yakiniku_walkin'] ?? '') }}"
                        placeholder="0">
                </div>
                <div class="col-md-4">
                    <label class="small text-muted">Event</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[dinner][cover_data][yakiniku_event]"
                        value="{{ old('session.dinner.cover_data.yakiniku_event', $dn->cover_data['yakiniku_event'] ?? '') }}"
                        placeholder="0">
                </div>
            </div>
        </div>

        {{-- <div class="p-3 border rounded mb-3 bg-white">
            <span class="badge bg-info mb-2">AYCE & CHILD</span>
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="small text-muted fw-bold">AYCE Total</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[dinner][cover_data][ayce_total]"
                        value="{{ old('session.dinner.cover_data.ayce_total', $dn->cover_data['ayce_total'] ?? '') }}"
                        placeholder="0">
                </div>
                <div class="col-md-3">
                    <label class="small text-muted">Child In-House</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[dinner][cover_data][child_inhouse]"
                        value="{{ old('session.dinner.cover_data.child_inhouse', $dn->cover_data['child_inhouse'] ?? '') }}"
                        placeholder="0">
                </div>
                <div class="col-md-3">
                    <label class="small text-muted">Child Walk-In</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[dinner][cover_data][child_walkin]"
                        value="{{ old('session.dinner.cover_data.child_walkin', $dn->cover_data['child_walkin'] ?? '') }}"
                        placeholder="0">
                </div>
                <div class="col-md-3">
                    <label class="small text-muted">Child Event</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[dinner][cover_data][child_event]"
                        value="{{ old('session.dinner.cover_data.child_event', $dn->cover_data['child_event'] ?? '') }}"
                        placeholder="0">
                </div>
            </div>
        </div> --}}

        <hr>

        <h6 class="fw-bold text-muted mt-3">2. Revenue Report (IDR)</h6>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label small">Food Revenue</label>
                <input type="text" class="form-control rupiah" name="session[dinner][revenue_food]"
                    value="{{ old('session.dinner.revenue_food', isset($dn->revenue_food) ? number_format($dn->revenue_food, 0, ',', '.') : '') }}"
                    placeholder="0" autocomplete="off">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Beverage Revenue</label>
                <input type="text" class="form-control rupiah" name="session[dinner][revenue_beverage]"
                    value="{{ old('session.dinner.revenue_beverage', isset($dn->revenue_beverage) ? number_format($dn->revenue_beverage, 0, ',', '.') : '') }}"
                    placeholder="0" autocomplete="off">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Others Revenue</label>
                <input type="text" class="form-control rupiah" name="session[dinner][revenue_others]"
                    value="{{ old('session.dinner.revenue_others', isset($dn->revenue_others) ? number_format($dn->revenue_others, 0, ',', '.') : '') }}"
                    placeholder="0" autocomplete="off">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Event Revenue</label>
                <input type="text" class="form-control rupiah" name="session[dinner][revenue_event]"
                    value="{{ old('session.dinner.revenue_event', isset($dn->revenue_event) ? number_format($dn->revenue_event, 0, ',', '.') : '') }}"
                    placeholder="0" autocomplete="off">
            </div>
        </div>

        <hr>

        <h6 class="fw-bold text-muted mt-3">3. Upselling & Remarks</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Upselling Menu (Food)</label>
                @php
                    $dnFoodVal = old('session.dinner.upselling_data.food', $dn->upselling_data['food'] ?? []);
                    $dnFoodJson = is_array($dnFoodVal) ? json_encode($dnFoodVal) : $dnFoodVal;
                @endphp
                <input type="hidden" id="input-dinner-food-NJR" name="session[dinner][upselling_data][food]"
                    value="{{ $dnFoodJson }}">

                <div class="input-group mb-2">
                    <select class="form-select form-select-sm" id="select-dinner-food-NJR">
                        <option value="" selected>Select Food...</option>
                        @foreach ($foods as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" class="form-control form-control-sm" id="pax-dinner-food-NJR"
                        placeholder="Qty" style="max-width: 70px;">
                    <button class="btn btn-sm btn-dark" type="button"
                        onclick="addUpsellingItem('dinner', 'food', 'NJR')"><i class="ti ti-plus"></i> Add</button>
                </div>
                <ul class="list-group small" id="list-dinner-food-NJR"></ul>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-bold">Beverage Upselling</label>
                @php
                    $dnBevVal = old('session.dinner.upselling_data.beverage', $dn->upselling_data['beverage'] ?? []);
                    $dnBevJson = is_array($dnBevVal) ? json_encode($dnBevVal) : $dnBevVal;
                @endphp
                <input type="hidden" id="input-dinner-beverage-NJR" name="session[dinner][upselling_data][beverage]"
                    value="{{ $dnBevJson }}">

                <div class="input-group mb-2">
                    <select class="form-select form-select-sm" id="select-dinner-beverage-NJR">
                        <option value="" selected>Select Drink...</option>
                        @foreach ($beverages as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" class="form-control form-control-sm" id="pax-dinner-beverage-NJR"
                        placeholder="Qty" style="max-width: 70px;">
                    <button class="btn btn-sm btn-dark" type="button"
                        onclick="addUpsellingItem('dinner', 'beverage', 'NJR')"><i class="ti ti-plus"></i>
                        Add</button>
                </div>
                <ul class="list-group small" id="list-dinner-beverage-NJR"></ul>
            </div>

            <div class="col-md-12 mt-3">
                <label class="form-label small fw-bold">VIP 1 & 2 List</label>
                @php
                    $dnVipVal = old('session.dinner.vip_remarks', $dn->vip_remarks ?? []);
                    $dnVipJson = is_array($dnVipVal) ? json_encode($dnVipVal) : $dnVipVal;
                @endphp
                <input type="hidden" id="input-vip-dinner-NJR" name="session[dinner][vip_remarks]"
                    value="{{ $dnVipJson }}">

                <div class="input-group mb-2">
                    <input type="text" class="form-control form-control-sm" id="vip-name-dinner-NJR"
                        placeholder="Guest Name (e.g. Mr. Budi)">
                    <input type="text" class="form-control form-control-sm" id="vip-pos-dinner-NJR"
                        placeholder="Position/Title (e.g. CEO)">
                    <button class="btn btn-sm btn-dark" type="button" onclick="addVipItem('dinner', 'NJR')"><i
                            class="ti ti-plus"></i> Add</button>
                </div>
                <ul class="list-group small" id="list-vip-dinner-NJR"></ul>
            </div>

            <div class="col-md-12">
                <label class="form-label small">General Remarks</label>
                <textarea class="form-control" name="session[dinner][remarks]">{{ old('session.dinner.remarks', $dn->remarks ?? '') }}</textarea>
            </div>

            <div class="col-md-12 mt-3">
                <label class="form-label small fw-bold">Staff on Duty</label>
                @php
                    $dnStaffVal = old('session.dinner.staff_on_duty', $dn->staff_on_duty ?? []);
                    $dnStaffJson = is_array($dnStaffVal) ? json_encode($dnStaffVal) : $dnStaffVal;
                @endphp
                <input type="hidden" id="input-staff-dinner-NJR" name="session[dinner][staff_on_duty]"
                    value="{{ $dnStaffJson }}">

                <div class="input-group mb-2">
                    <select class="form-select form-select-sm" id="select-staff-dinner-NJR">
                        <option value="" selected>Select Staff...</option>
                        @foreach ($myStaffList as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-dark" type="button" onclick="addStaffItem('dinner', 'NJR')"><i
                            class="ti ti-plus"></i> Add</button>
                </div>
                <div id="list-staff-dinner-NJR" class="d-flex flex-wrap"></div>
            </div>
        </div>

        <hr>

        <h6 class="fw-bold text-muted mt-3">4. Competitor Comparison</h6>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small">Shangri-La</label>
                <input type="number" class="form-control" name="session[dinner][competitor_data][shangrila_cover]"
                    value="{{ old('session.dinner.competitor_data.shangrila_cover', $dn->competitor_data['shangrila_cover'] ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small">JW Marriott</label>
                <input type="number" class="form-control" name="session[dinner][competitor_data][jw_marriott_cover]"
                    value="{{ old('session.dinner.competitor_data.jw_marriott_cover', $dn->competitor_data['jw_marriott_cover'] ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Sheraton</label>
                <input type="number" class="form-control" name="session[dinner][competitor_data][sheraton_cover]"
                    value="{{ old('session.dinner.competitor_data.sheraton_cover', $dn->competitor_data['sheraton_cover'] ?? '') }}">
            </div>
        </div>

    </div>
</div>

{{-- ============================================================ --}}
{{-- SCRIPT INITIALIZATION --}}
{{-- ============================================================ --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // --- LUNCH INIT ---

        // Upselling Food
        let lcFood = {!! json_encode(old('session.lunch.upselling_data.food', $lc->upselling_data['food'] ?? [])) !!};
        initUpselling('lunch', 'food', lcFood, 'NJR');

        // Upselling Beverage
        let lcBev = {!! json_encode(old('session.lunch.upselling_data.beverage', $lc->upselling_data['beverage'] ?? [])) !!};
        initUpselling('lunch', 'beverage', lcBev, 'NJR');

        // VIP
        let lcVip = {!! json_encode(old('session.lunch.vip_remarks', $lc->vip_remarks ?? [])) !!};
        initVip('lunch', lcVip, 'NJR');

        // Staff
        let lcStaff = {!! json_encode(old('session.lunch.staff_on_duty', $lc->staff_on_duty ?? [])) !!};
        initStaff('lunch', lcStaff, 'NJR');


        // --- DINNER INIT ---

        // Upselling Food
        let dnFood = {!! json_encode(old('session.dinner.upselling_data.food', $dn->upselling_data['food'] ?? [])) !!};
        initUpselling('dinner', 'food', dnFood, 'NJR');

        // Upselling Beverage
        let dnBev = {!! json_encode(old('session.dinner.upselling_data.beverage', $dn->upselling_data['beverage'] ?? [])) !!};
        initUpselling('dinner', 'beverage', dnBev, 'NJR');

        // VIP
        let dnVip = {!! json_encode(old('session.dinner.vip_remarks', $dn->vip_remarks ?? [])) !!};
        initVip('dinner', dnVip, 'NJR');

        // Staff
        let dnStaff = {!! json_encode(old('session.dinner.staff_on_duty', $dn->staff_on_duty ?? [])) !!};
        initStaff('dinner', dnStaff, 'NJR');

    });
</script>
