@extends('layouts.mantis')

@section('header')
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Edit Daily Report (Draft)</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('daily-reports.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item" aria-current="page">Edit Report</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ERROR SYSTEM --}}
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            {{-- FORM EDIT: Menggunakan Method PUT --}}
            <form action="{{ route('daily-reports.update', $dailyReport->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- CARD 1: General Info --}}
                <div class="card">
                    <div class="card-header">
                        <h5>General Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Restaurant</label>
                                    {{-- Disabled karena resto tidak boleh diganti saat edit agar form tidak rusak --}}
                                    <input type="text" class="form-control" value="{{ $dailyReport->restaurant->name }}"
                                        disabled>
                                    {{-- Kirim ID sebagai hidden jika perlu, tapi di controller kita pakai data lama --}}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Date</label>
                                    <input type="date" name="date" class="form-control"
                                        value="{{ old('date', $dailyReport->date->format('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- INCLUDE PARTIAL FORM SESUAI KODE RESTO --}}
                {{-- Logika: Kita include langsung file berdasarkan kode restoran --}}

                <div id="form-container">
                    @php
                        $code = $dailyReport->restaurant->code; // 209, NGN, XFH
                        $viewName = 'daily-reports.partials.form-' . strtolower($code === 'NJR' ? 'nagano' : $code);
                        // Note: sesuaikan logika nama file Anda di sini.
                        // Jika code 209 -> form-209.blade.php
                        // Jika code NGN -> form-nagano.blade.php (jika namanya itu)
                    @endphp

                    @if (View::exists($viewName))
                        @include($viewName)
                    @else
                        @include('daily-reports.partials.form-' . strtolower($dailyReport->restaurant->code))
                    @endif
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="card mt-3">
                    <div class="card-body text-end">
                        <a href="{{ route('daily-reports.index') }}" class="btn btn-light me-2">Cancel</a>

                        <button type="submit" name="action" value="draft" class="btn btn-secondary me-2">
                            <i class="ti ti-device-floppy"></i> Update Draft
                        </button>
                        <button type="submit" name="action" value="submit" class="btn btn-primary">
                            <i class="ti ti-send"></i> Submit Final Report
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <script>
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('rupiah')) {
                let value = e.target.value;

                // 1. Hapus karakter selain angka
                let numberString = value.replace(/[^,\d]/g, '').toString();

                // 2. Format menjadi ribuan dengan titik
                let split = numberString.split(',');
                let sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;

                // 3. Kembalikan nilai yang sudah diformat ke input
                e.target.value = rupiah;
            }
        });

        // --- UPSELLING MANAGER SCRIPT ---
        // Variabel global untuk menyimpan state data
        let upsellingState = {};

        /**
         * Fungsi Inisialisasi (Dipanggil saat load halaman)
         * Berguna untuk memuat data lama (saat Edit atau Error validation)
         */
        window.initUpselling = function(session, type, initialData) {
            const key = `${session}_${type}`;
            // Pastikan initialData adalah array, jika null/undefined jadikan array kosong
            upsellingState[key] = initialData || [];
            renderUpsellingTable(session, type);
        };

        /**
         * Fungsi Menambahkan Item
         */
        window.addUpsellingItem = function(session, type) {
            const selectId = `select-${session}-${type}`;
            const paxId = `pax-${session}-${type}`;

            const selectEl = document.getElementById(selectId);
            const paxEl = document.getElementById(paxId);

            const itemId = selectEl.value;
            const itemName = selectEl.options[selectEl.selectedIndex].text;
            const pax = parseInt(paxEl.value);

            // Validasi sederhana
            if (!itemId) {
                alert("Please select a menu item.");
                return;
            }
            if (!pax || pax < 1) {
                alert("Please enter valid pax/quantity.");
                return;
            }

            // Tambahkan ke State
            const key = `${session}_${type}`;
            if (!upsellingState[key]) upsellingState[key] = [];

            upsellingState[key].push({
                id: itemId,
                name: itemName,
                pax: pax
            });

            // Reset Input
            selectEl.value = "";
            paxEl.value = "";

            // Render Ulang Tabel & Update Hidden Input
            renderUpsellingTable(session, type);
        };

        /**
         * Fungsi Menghapus Item
         */
        window.removeUpsellingItem = function(session, type, index) {
            const key = `${session}_${type}`;
            upsellingState[key].splice(index, 1); // Hapus array index tsb
            renderUpsellingTable(session, type);
        };

        /**
         * Render Tabel HTML & Update Hidden Input
         */
        function renderUpsellingTable(session, type) {
            const key = `${session}_${type}`;
            const data = upsellingState[key];

            // 1. Update Hidden Input (Ini yang dikirim ke Server)
            const hiddenInputId = `input-${session}-${type}`;
            document.getElementById(hiddenInputId).value = JSON.stringify(data);

            // 2. Render Tampilan List
            const listId = `list-${session}-${type}`;
            const listEl = document.getElementById(listId);
            listEl.innerHTML = ""; // Bersihkan list

            data.forEach((item, index) => {
                const li = document.createElement('li');
                li.className = "list-group-item d-flex justify-content-between align-items-center p-2";
                li.innerHTML = `
                <div>
                    <span class="fw-bold">${item.name}</span>
                    <span class="badge bg-primary rounded-pill ms-2">${item.pax} Pax</span>
                </div>
                <button type="button" class="btn btn-sm btn-link-danger p-0"
                    onclick="removeUpsellingItem('${session}', '${type}', ${index})">
                    <i class="ti ti-x"></i>
                </button>
            `;
                listEl.appendChild(li);
            });
        }

        // --- VIP REMARKS MANAGER SCRIPT ---

        // Variabel global untuk menyimpan state data VIP
        let vipState = {};

        /**
         * Fungsi Inisialisasi VIP
         * Dipanggil dari form-partial saat load halaman
         */
        window.initVip = function(session, initialData) {
            const key = session; // Key hanya berdasarkan sesi (breakfast/lunch/dinner)

            // Pastikan initialData adalah array
            vipState[key] = initialData || [];
            renderVipTable(session);
        };

        /**
         * Fungsi Menambahkan Item VIP
         */
        window.addVipItem = function(session) {
            // ID Element Input
            const nameId = `vip-name-${session}`;
            const posId = `vip-pos-${session}`;

            const nameEl = document.getElementById(nameId);
            const posEl = document.getElementById(posId);

            const nameVal = nameEl.value.trim();
            const posVal = posEl.value.trim();

            // Validasi sederhana
            if (!nameVal) {
                alert("Please enter Guest Name.");
                return;
            }
            if (!posVal) {
                alert("Please enter Position/Title.");
                return;
            }

            // Tambahkan ke State
            const key = session;
            if (!vipState[key]) vipState[key] = [];

            vipState[key].push({
                name: nameVal,
                position: posVal
            });

            // Reset Input agar siap input berikutnya
            nameEl.value = "";
            posEl.value = "";
            nameEl.focus(); // Arahkan kursor kembali ke nama

            // Render Ulang Tabel & Update Hidden Input
            renderVipTable(session);
        };

        /**
         * Fungsi Menghapus Item VIP
         */
        window.removeVipItem = function(session, index) {
            const key = session;
            vipState[key].splice(index, 1); // Hapus array index tsb
            renderVipTable(session);
        };

        /**
         * Render Tampilan List & Update Hidden Input
         */
        function renderVipTable(session) {
            const key = session;
            const data = vipState[key];

            // 1. Update Hidden Input (Ini yang dikirim ke Server)
            // ID Hidden Input: input-vip-breakfast
            const hiddenInputId = `input-vip-${session}`;
            const hiddenInput = document.getElementById(hiddenInputId);

            if (hiddenInput) {
                hiddenInput.value = JSON.stringify(data);
            }

            // 2. Render Tampilan List (<ul>)
            // ID List: list-vip-breakfast
            const listId = `list-vip-${session}`;
            const listEl = document.getElementById(listId);

            if (listEl) {
                listEl.innerHTML = ""; // Bersihkan list

                data.forEach((item, index) => {
                    const li = document.createElement('li');
                    li.className =
                        "list-group-item d-flex justify-content-between align-items-start p-2 bg-light mb-1 border rounded";
                    li.innerHTML = `
                    <div class="ms-2 me-auto">
                        <div class="fw-bold">${item.name}</div>
                        <span class="small text-muted">${item.position}</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-link-danger p-0"
                        onclick="removeVipItem('${session}', ${index})">
                        <i class="ti ti-x fs-4"></i>
                    </button>
                `;
                    listEl.appendChild(li);
                });
            }
        }

        // --- STAFF ON DUTY MANAGER SCRIPT ---
        let staffState = {};

        window.initStaff = function(session, initialData) {
            staffState[session] = initialData || [];
            renderStaffTable(session);
        };

        window.addStaffItem = function(session) {
            const selectId = `select-staff-${session}`;
            const selectEl = document.getElementById(selectId);

            const staffName = selectEl.options[selectEl.selectedIndex].text;
            const staffId = selectEl.value; // Kita simpan Namanya saja atau ID, sesuai kebutuhan.
            // Disini saya simpan Nama agar di report tercetak nama jelas.

            if (!staffId) {
                alert("Please select a staff member.");
                return;
            }

            // Cek duplikasi (agar tidak add orang yang sama 2x)
            if (staffState[session].includes(staffName)) {
                alert("Staff member already added.");
                return;
            }

            staffState[session].push(staffName);
            selectEl.value = ""; // Reset dropdown
            renderStaffTable(session);
        };

        window.removeStaffItem = function(session, index) {
            staffState[session].splice(index, 1);
            renderStaffTable(session);
        };

        function renderStaffTable(session) {
            const data = staffState[session];

            // Update Hidden Input
            const hiddenInput = document.getElementById(`input-staff-${session}`);
            if (hiddenInput) hiddenInput.value = JSON.stringify(data);

            // Render UI
            const listEl = document.getElementById(`list-staff-${session}`);
            if (listEl) {
                listEl.innerHTML = "";
                data.forEach((name, index) => {
                    const badge = document.createElement('span');
                    badge.className = "badge bg-light-primary text-primary me-1 mb-1 fs-6";
                    badge.innerHTML = `
                    ${name}
                    <i class="ti ti-x ms-1" style="cursor:pointer" onclick="removeStaffItem('${session}', ${index})"></i>
                `;
                    listEl.appendChild(badge);
                });
            }
        }
    </script>
@endsection
