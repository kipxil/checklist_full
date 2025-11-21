<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\DailyReportDetail;
use App\Models\Restaurant;
use App\Models\UpsellingItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DailyReportController extends Controller
{
    //
    public function index()
    {
        $reports = DailyReport::with(['restaurant', 'user'])
            ->orderBy('date', 'desc')
            ->paginate(10);

        return view('daily-reports.index', compact('reports'));
    }

    public function create()
    {
        $user = Auth::user();

        if ($user->hasRole('Super Admin')) {
            $restaurants = Restaurant::all();
        } else {
            // User biasa hanya dapat restorannya sendiri
            $restaurants = $user->restaurants;
        }

        $details = [];

        $upsellingQuery = UpsellingItem::query();
        if (!$user->hasRole('Super Admin')) {
            $upsellingQuery->whereIn('restaurant_id', $user->restaurants->pluck('id'));
        }
        $upsellingItems = $upsellingQuery->get()->groupBy('restaurant_id');

        $staffQuery = User::query();

        // Jika bukan Super Admin, hanya ambil staff dari restorannya sendiri
        if ($user->restaurant_id && !$user->hasRole('Super Admin')) {
            $staffQuery->where('restaurant_id', $user->restaurant_id);
        }

        // Kita ambil ID dan Name saja, group by restaurant_id
        $staffList = $staffQuery->get()->groupBy('restaurant_id');

        // dd($staffList);

        return view('daily-reports.create', compact('restaurants', 'details', 'upsellingItems', 'staffList'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // PROTEKSI DATA
        // Jika user terikat restoran, paksa input restaurant_id sesuai user
        if (!$user->hasRole('Super Admin')) {
            // Cek apakah ID yang dikirim form benar-benar milik user ini?
            $myRestoIds = $user->restaurants->pluck('id')->toArray();

            if (!in_array($request->restaurant_id, $myRestoIds)) {
                return back()->with('error', 'Anda tidak memiliki akses ke restoran ini.');
            }
        }

        // 1. Validasi Dasar (Header)
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'date' => 'required|date',
            // Validasi detail akan kita lakukan manual atau terpisah karena struktur dinamis
        ]);

        $rawSessions = $request->input('session', []);
        $inputSessions = $this->sanitizeSessionData($rawSessions);
        $request->merge(['session' => $inputSessions]);

        $this->validateSubmission($request);

        // Menggunakan DB Transaction agar jika gagal simpan detail, header tidak terbuat
        try {
            DB::beginTransaction();

            // 2. Tentukan Status (Draft vs Submitted)
            // Kita cek tombol mana yang diklik di form (nanti di form kasih name="action")
            // $status = $request->input('action') === 'submit' ? 'submitted' : 'draft';
            $status = 'draft';
            $approvedBy = null;
            $approvedAt = null;

            // Jika user menekan tombol SUBMIT (Bukan Draft)
            if ($request->input('action') === 'submit') {
                $user = Auth::user();

                // Cek Role User
                if ($this->isUserApprover($user)) {
                    // Jika Manager/Approver -> Langsung Approved
                    $status = 'approved';
                    $approvedBy = $user->id;
                    $approvedAt = now();
                } else {
                    // Jika Staff Biasa -> Submitted (Butuh Approval)
                    $status = 'submitted';
                }
            }

            // 3. Simpan Header Laporan (Tabel daily_reports)
            $report = DailyReport::create([
                'restaurant_id' => $request->restaurant_id,
                'user_id' => Auth::id(),
                'date' => $request->date,
                'status' => $status,
                'approved_by' => $approvedBy,
                'approved_at' => $approvedAt,
            ]);

            // 4. Simpan Detail Sesi (Looping Breakfast, Lunch, Dinner)
            // Kita asumsikan form mengirim data dengan struktur: name="session[breakfast][revenue_food]"
            $sessions = ['breakfast', 'lunch', 'dinner'];
            // $inputSessions = $request->input('session', []);

            foreach ($sessions as $sessionType) {
                // Cek apakah ada data input untuk sesi ini
                if (isset($inputSessions[$sessionType])) {
                    $data = $inputSessions[$sessionType];

                    // Logika Hybrid: Mapping input form ke kolom database
                    DailyReportDetail::create([
                        'daily_report_id' => $report->id,
                        'session_type' => $sessionType,

                        // Data Revenue (Decimal)
                        'revenue_food' => $data['revenue_food'] ?? 0,
                        'revenue_beverage' => $data['revenue_beverage'] ?? 0,
                        'revenue_others' => $data['revenue_others'] ?? 0,
                        'revenue_event' => $data['revenue_event'] ?? 0,

                        // Data JSON (Array otomatis dicast ke JSON oleh Model)
                        'cover_data' => $data['cover_data'] ?? [],
                        'upselling_data' => $data['upselling_data'] ?? [],
                        'competitor_data' => $data['competitor_data'] ?? [],
                        'additional_data' => $data['additional_data'] ?? [],

                        // Data Text
                        'thematic' => $data['thematic'] ?? null,
                        'staff_on_duty' => $data['staff_on_duty'] ?? null,
                        'remarks' => $data['remarks'] ?? null,
                        'vip_remarks' => $data['vip_remarks'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('daily-reports.index')
                ->with('success', 'Laporan berhasil disimpan sebagai ' . ucfirst($status));
        } catch (\Exception $e) {
            DB::rollBack();
            // Debugging: Matikan comment di bawah jika ingin lihat error asli di layar
            // dd($e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(DailyReport $dailyReport)
    {
        $user = Auth::user();
        // 1. Cek Policy: Hanya status Draft yang boleh diedit
        if ($dailyReport->status !== 'draft') {
            return redirect()->route('daily-reports.index')
                ->with('error', 'Laporan yang sudah disubmit atau diapprove tidak dapat diedit.');
        }

        // 2. Load data detail & relasi restoran
        // Kita perlu data restoran untuk mengisi dropdown (walaupun nanti sebaiknya didisabled)
        $dailyReport->load(['details', 'restaurant']);

        if ($user->hasRole('Super Admin')) {
            // Super Admin lihat semua
            $restaurants = Restaurant::all();
        } else {
            // User Biasa / Cluster Manager lihat restoran miliknya saja
            // Kita akses langsung relasi restaurants
            $restaurants = $user->restaurants;
        }

        // 3. RE-MAPPING DATA DETAIL (PENTING)
        // Kita ubah Collection detail menjadi array dengan key session_type
        // Contoh hasil: ['breakfast' => {OBJECT DATA}, 'lunch' => {OBJECT DATA}]
        $details = $dailyReport->details->keyBy('session_type');

        $upsellingQuery = UpsellingItem::query();
        if (!$user->hasRole('Super Admin')) {
            $upsellingQuery->whereIn('restaurant_id', $user->restaurants->pluck('id'));
        }
        $upsellingItems = $upsellingQuery->get()->groupBy('restaurant_id');

        $staffList = User::all()->groupBy('restaurant_id');

        return view('daily-reports.edit', compact('dailyReport', 'restaurants', 'details', 'upsellingItems', 'staffList'));
    }

    public function update(Request $request, DailyReport $dailyReport)
    {
        // 1. Cek status lagi untuk keamanan
        if ($dailyReport->status !== 'draft') {
            return back()->with('error', 'Hanya laporan Draft yang bisa diupdate.');
        }

        $request->validate([
            'date' => 'required|date',
            // Restaurant ID biasanya tidak diubah saat edit, tapi kalau mau divalidasi silakan
        ]);

        $rawSessions = $request->input('session', []);
        $inputSessions = $this->sanitizeSessionData($rawSessions);
        $request->merge(['session' => $inputSessions]);

        $this->validateSubmission($request);

        try {
            DB::beginTransaction();

            // 2. Tentukan Status Baru
            // User bisa saja klik "Save Draft" lagi (tetap draft) atau klik "Submit" (berubah jadi submitted)
            $status = 'draft';
            $approvedBy = null;
            $approvedAt = null;

            if ($request->input('action') === 'submit') {
                $user = Auth::user();
                if ($this->isUserApprover($user)) {
                    $status = 'approved';
                    $approvedBy = $user->id;
                    $approvedAt = now();
                } else {
                    $status = 'submitted';
                }
            }

            // 3. Update Header
            $dailyReport->update([
                'date' => $request->date,
                'status' => $status,
                'approved_by' => $approvedBy,
                'approved_at' => $approvedAt,
                // 'restaurant_id' => ... (Biasanya resto tidak diubah saat edit, agar tidak merusak struktur form)
            ]);

            // 4. Update Detail Sesi
            // STRATEGI: Hapus semua detail lama, lalu input ulang yang baru.
            // Ini jauh lebih aman daripada logic update satu-satu untuk kasus JSON dinamis.
            $dailyReport->details()->delete();

            // --- Mulai Copy-Paste Logika dari Store ---
            $sessions = ['breakfast', 'lunch', 'dinner'];
            // $inputSessions = $request->input('session', []);

            foreach ($sessions as $sessionType) {
                if (isset($inputSessions[$sessionType])) {
                    $data = $inputSessions[$sessionType];

                    DailyReportDetail::create([
                        'daily_report_id' => $dailyReport->id,
                        'session_type' => $sessionType,

                        'revenue_food' => $data['revenue_food'] ?? 0,
                        'revenue_beverage' => $data['revenue_beverage'] ?? 0,
                        'revenue_others' => $data['revenue_others'] ?? 0,
                        'revenue_event' => $data['revenue_event'] ?? 0,

                        'cover_data' => $data['cover_data'] ?? [],
                        'upselling_data' => $data['upselling_data'] ?? [],
                        'competitor_data' => $data['competitor_data'] ?? [],
                        'additional_data' => $data['additional_data'] ?? [],

                        'thematic' => $data['thematic'] ?? null,
                        'staff_on_duty' => $data['staff_on_duty'] ?? null,
                        'remarks' => $data['remarks'] ?? null,
                        'vip_remarks' => $data['vip_remarks'] ?? null,
                    ]);
                }
            }
            // --- Selesai Copy-Paste Logika Store ---

            DB::commit();

            return redirect()->route('daily-reports.index')
                ->with('success', 'Laporan berhasil diperbarui status menjadi: ' . ucfirst($status));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Update gagal: ' . $e->getMessage())->withInput();
        }
    }

    public function show(DailyReport $dailyReport)
    {
        // Load detail relasi
        $dailyReport->load(['details', 'restaurant', 'user']);

        return view('daily-reports.show', compact('dailyReport'));
    }

    public function approve(DailyReport $dailyReport)
    {
        // 1. Validasi: Hanya laporan 'submitted' yang bisa di-approve
        // Laporan 'draft' harus disubmit dulu oleh pembuatnya
        if ($dailyReport->status !== 'submitted') {
            return back()->with('error', 'Hanya laporan dengan status Submitted yang bisa disetujui.');
        }

        // 2. Update Status
        $dailyReport->update([
            'status' => 'approved',
            'approved_by' => Auth::id(), // Mengambil ID user yang sedang login (Manager)
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Laporan berhasil disetujui (Approved).');
    }

    public function reject(DailyReport $dailyReport)
    {
        // 1. Validasi: Hanya laporan 'submitted' (atau approved) yang bisa di-reject
        if ($dailyReport->status === 'draft') {
            return back()->with('error', 'Laporan status Draft tidak perlu di-reject.');
        }

        // 2. Update Status kembali ke Draft
        $dailyReport->update([
            'status' => 'draft',
            'approved_by' => null, // Reset approver
            'approved_at' => null, // Reset waktu approve
        ]);

        return back()->with('success', 'Laporan ditolak dan status kembali menjadi Draft. User dapat mengeditnya sekarang.');
    }

    private function validateSubmission(Request $request)
    {
        // Jika user hanya ingin Save Draft, kita skip validasi ketat
        if ($request->input('action') === 'draft') {
            return;
        }

        // Aturan validasi untuk Submit
        $request->validate([
            // Wajib ada minimal satu sesi yang diisi
            'session' => 'required|array',

            // Validasi Revenue (Wajib angka dan minimal 0)
            'session.*.revenue_food' => 'required|numeric|min:0',
            'session.*.revenue_beverage' => 'required|numeric|min:0',

            // Validasi Text Penting
            'session.*.staff_on_duty' => 'required|string',

            // Validasi Cover (Kita cek salah satu field kunci saja, misal Total Actual)
            // Sesuaikan dengan field yang pasti ada di semua resto, atau gunakan logic spesifik
            // Contoh di bawah: Memastikan JSON cover_data tidak kosong/null
            'session.*.cover_data' => 'required',
        ], [
            // Custom Error Messages agar lebih mudah dibaca user
            'session.required' => 'Mohon isi minimal satu sesi laporan.',
            'session.*.revenue_food.required' => 'Food Revenue wajib diisi (isi 0 jika tidak ada).',
            'session.*.staff_on_duty.required' => 'Staff on Duty wajib diisi.',
        ]);
    }

    private function sanitizeSessionData($inputSessions)
    {
        if (!$inputSessions) return [];

        $cleanedSessions = [];
        foreach ($inputSessions as $session => $data) {
            $cleanedData = $data;

            // Daftar kolom yang mengandung uang
            $moneyFields = ['revenue_food', 'revenue_beverage', 'revenue_others', 'revenue_event'];

            foreach ($moneyFields as $field) {
                if (isset($cleanedData[$field])) {
                    // Hapus titik (.)
                    // Jika Anda pakai format US (koma sebagai ribuan), ganti '.' jadi ','
                    $cleanedData[$field] = str_replace('.', '', $cleanedData[$field]);
                }
            }
            $cleanedSessions[$session] = $cleanedData;
        }

        return $cleanedSessions;
    }

    private function isUserApprover($user)
    {
        return $user->hasRole([
            'Super Admin',
            'Restaurant Manager',
            'Assistant Restaurant Manager',
            'F&B Supervisor'
        ]);
    }
}
