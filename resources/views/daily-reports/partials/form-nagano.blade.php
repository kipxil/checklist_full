{{-- FORM KHUSUS NAGANO (NGN) --}}

{{-- === SESSION: LUNCH === --}}
<div class="card">
    <div class="card-header bg-light">
        <h5 class="mb-0 text-danger"><i class="ti ti-soup"></i> Nagano - Lunch Report</h5>
    </div>
    <div class="card-body">

        {{-- STRUKTUR COVER KOMPLEKS --}}
        <h6 class="fw-bold text-muted mt-3">1. Cover Report Details</h6>

        {{-- Teppanyaki Section --}}
        <div class="p-2 border rounded mb-2 bg-white">
            <span class="badge bg-dark mb-2">TEPPANYAKI</span>
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="number" class="form-control form-control-sm" placeholder="In-House (Adult)"
                        name="session[lunch][cover_data][teppanyaki_inhouse]">
                </div>
                <div class="col-md-4">
                    <input type="number" class="form-control form-control-sm" placeholder="Walk-In (Adult)"
                        name="session[lunch][cover_data][teppanyaki_walkin]">
                </div>
                <div class="col-md-4">
                    <input type="number" class="form-control form-control-sm" placeholder="Event (Adult)"
                        name="session[lunch][cover_data][teppanyaki_event]">
                </div>
            </div>
        </div>

        {{-- Yakiniku Section --}}
        <div class="p-2 border rounded mb-2 bg-white">
            <span class="badge bg-danger mb-2">YAKINIKU</span>
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="number" class="form-control form-control-sm" placeholder="In-House (Adult)"
                        name="session[lunch][cover_data][yakiniku_inhouse]">
                </div>
                <div class="col-md-4">
                    <input type="number" class="form-control form-control-sm" placeholder="Walk-In (Adult)"
                        name="session[lunch][cover_data][yakiniku_walkin]">
                </div>
                <div class="col-md-4">
                    <input type="number" class="form-control form-control-sm" placeholder="Event (Adult)"
                        name="session[lunch][cover_data][yakiniku_event]">
                </div>
            </div>
        </div>

        {{-- AYCE & Child Section --}}
        <div class="p-2 border rounded mb-2 bg-white">
            <span class="badge bg-info mb-2">AYCE & GENERAL</span>
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="small">AYCE Total (Adult)</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[lunch][cover_data][ayce_total]">
                </div>
                <div class="col-md-3">
                    <label class="small">Child In-House</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[lunch][cover_data][child_inhouse]">
                </div>
                <div class="col-md-3">
                    <label class="small">Child Walk-In</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[lunch][cover_data][child_walkin]">
                </div>
                <div class="col-md-3">
                    <label class="small">Child Event</label>
                    <input type="number" class="form-control form-control-sm"
                        name="session[lunch][cover_data][child_event]">
                </div>
            </div>
        </div>
        <hr>

        {{-- Revenue (Sama seperti resto lain) --}}
        <h6 class="fw-bold text-muted mt-3">2. Revenue Report (IDR)</h6>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label small">Food</label>
                <input type="number" class="form-control" name="session[lunch][revenue_food]">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Beverage</label>
                <input type="number" class="form-control" name="session[lunch][revenue_beverage]">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Others</label>
                <input type="number" class="form-control" name="session[lunch][revenue_others]">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Event</label>
                <input type="number" class="form-control" name="session[lunch][revenue_event]">
            </div>
        </div>

        {{-- ... (Bagian Upselling, Competitor, dll sama polanya) ... --}}
    </div>
</div>

{{-- === COPY UNTUK DINNER === --}}
