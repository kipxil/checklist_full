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
use Barryvdh\DomPDF\Facade\Pdf;

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
            $restaurants = Restaurant::with('users')->get();
        } else {
            // User biasa hanya dapat restorannya sendiri
            $restaurants = $user->restaurants()->with('users')->get();
        }

        $details = [];

        $upsellingQuery = UpsellingItem::query();
        if (!$user->hasRole('Super Admin')) {
            $upsellingQuery->whereIn('restaurant_id', $user->restaurants->pluck('id'));
        }
        $upsellingItems = $upsellingQuery->get()->groupBy('restaurant_id');

        return view('daily-reports.create', compact('restaurants', 'details', 'upsellingItems'));
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

        // $request->merge(['date' => now()->format('Y-m-d H:i:s')]);

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
        // 1. Cek Policy: Hanya status Draft yang boleh diedit
        if ($dailyReport->status !== 'draft') {
            return redirect()->route('daily-reports.index')
                ->with('error', 'Laporan yang sudah disubmit atau diapprove tidak dapat diedit.');
        }

        // 2. Load data detail & relasi restoran
        // Kita perlu data restoran untuk mengisi dropdown (walaupun nanti sebaiknya didisabled)
        $dailyReport->load(['details', 'restaurant']);

        $user = Auth::user();
        if ($user->hasRole('Super Admin')) {
            // Super Admin lihat semua
            $restaurants = Restaurant::with('users')->get();
        } else {
            // User Biasa / Cluster Manager lihat restoran miliknya saja
            // Kita akses langsung relasi restaurants
            $restaurants = $user->restaurants()->with('users')->get();
        }

        // 3. RE-MAPPING DATA DETAIL (PENTING)
        // Kita ubah Collection detail menjadi array dengan key session_type
        // Contoh hasil: ['breakfast' => {OBJECT DATA}, 'lunch' => {OBJECT DATA}]
        $details = $dailyReport->details->keyBy('session_type');

        $upsellingItems = UpsellingItem::all()->groupBy('restaurant_id');

        return view('daily-reports.edit', compact('dailyReport', 'restaurants', 'details', 'upsellingItems'));
    }

    public function update(Request $request, DailyReport $dailyReport)
    {
        // 1. Cek status lagi untuk keamanan
        if ($dailyReport->status !== 'draft') {
            return back()->with('error', 'Hanya laporan Draft yang bisa diupdate.');
        }

        // Jika ingin mengupdate jam saat diedit (Opsional, kalau tidak mau jam berubah, hapus baris ini)
        // $request->merge(['date' => now()->format('Y-m-d H:i:s')]);

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

    public function destroy(DailyReport $dailyReport)
    {
        $user = Auth::user();

        // 1. Proteksi Role: Hanya Super Admin & Manager yang boleh hapus
        if (!$user->hasRole(['Super Admin', 'Restaurant Manager'])) {
            abort(403, 'Unauthorized. Only Managers can delete reports.');
        }

        // 2. Eksekusi Hapus
        // Karena relasi di migration pakai 'onDelete("cascade")',
        // detail laporan akan otomatis ikut terhapus.
        $dailyReport->delete();

        return back()->with('success', 'Laporan berhasil dihapus.');
    }

    public function approve(DailyReport $dailyReport)
    {
        if (!$this->isUserApprover(Auth::user())) {
            abort(403, 'Unauthorized action. Only Managers can approve reports.');
        }
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
        if (!$this->isUserApprover(Auth::user())) {
            abort(403, 'Unauthorized action. Only Managers can reject reports.');
        }
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

    public function downloadPdf(DailyReport $dailyReport)
    {
        // 1. Validasi: Hanya yang Approved yang boleh didownload
        if ($dailyReport->status !== 'approved') {
            return back()->with('error', 'Hanya laporan yang sudah disetujui (Approved) yang dapat diunduh.');
        }

        // 2. Load Relasi Data
        $dailyReport->load(['details', 'restaurant', 'user', 'approver']);

        // 3. Generate PDF
        // Kita pakai view khusus 'daily-reports.pdf' agar tampilannya bersih (format surat)
        $pdf = Pdf::loadView('daily-reports.pdf', compact('dailyReport'));

        // Setup kertas A4 Portrait
        $pdf->setPaper('a4', 'portrait');

        // 4. Download dengan nama file yang rapi
        // Contoh: Report_209Dining_2025-11-27.pdf
        $filename = 'Report_' . str_replace(' ', '', $dailyReport->restaurant->code) . '_' . $dailyReport->date->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    private function validateSubmission(Request $request)
    {
        // Jika tombol Draft, lewati semua validasi
        if ($request->input('action') === 'draft') {
            return;
        }

        // Aturan Validasi Wajib (Required)
        $rules = [
            'session' => 'required|array', // Wajib ada sesi

            // 1. COVER REPORT (Wajib diisi/Array tidak boleh kosong)
            // Kita cek apakah array cover_data ada isinya
            'session.*.cover_data' => 'required|array',

            // 2. REVENUE REPORT (Wajib diisi & Angka)
            'session.*.revenue_food' => 'required|numeric|min:0',
            'session.*.revenue_beverage' => 'required|numeric|min:0',
            'session.*.revenue_others' => 'required|numeric|min:0',
            'session.*.revenue_event' => 'required|numeric|min:0',

            // 3. GENERAL REMARKS (Wajib Teks)
            'session.*.remarks' => 'required|string',

            // 4. STAFF ON DUTY (Wajib pilih minimal 1 orang)
            'session.*.staff_on_duty' => 'required|array|min:1',

            // 5. COMPETITOR COMPARISON (Wajib diisi)
            'session.*.competitor_data' => 'required|array',
        ];

        // Pesan Error Bahasa Indonesia (Opsional, agar lebih jelas)
        $messages = [
            'session.*.cover_data.required' => 'Cover Report details wajib diisi.',
            'session.*.revenue_food.required' => 'Food Revenue wajib diisi (isi 0 jika tidak ada).',
            'session.*.revenue_beverage.required' => 'Beverage Revenue wajib diisi (isi 0 jika tidak ada).',
            'session.*.remarks.required' => 'General Remarks wajib diisi.',
            'session.*.staff_on_duty.min' => 'Staff on Duty wajib dipilih minimal 1 orang.',
            'session.*.competitor_data.required' => 'Competitor Comparison wajib diisi.',
        ];

        // Jalankan Validasi
        $request->validate($rules, $messages);
    }

    private function sanitizeSessionData($inputSessions)
    {
        if (!$inputSessions) return [];

        $cleanedSessions = [];
        foreach ($inputSessions as $session => $data) {
            $cleanedData = $data;

            // 1. BERSIHKAN UANG (Hapus Titik)
            $moneyFields = ['revenue_food', 'revenue_beverage', 'revenue_others', 'revenue_event'];
            foreach ($moneyFields as $field) {
                if (isset($cleanedData[$field])) {
                    $cleanedData[$field] = str_replace('.', '', $cleanedData[$field]);
                }
            }

            // 2. DECODE JSON STRING MENJADI ARRAY (Agar bisa divalidasi)
            // Kolom-kolom ini dikirim sebagai string JSON "[...]" oleh JavaScript
            $jsonFields = ['staff_on_duty', 'upselling_data', 'vip_remarks'];
            foreach ($jsonFields as $field) {
                if (isset($cleanedData[$field]) && is_string($cleanedData[$field])) {
                    $decoded = json_decode($cleanedData[$field], true);
                    // Jika decode berhasil, pakai arraynya. Jika gagal, biarkan string/null.
                    $cleanedData[$field] = $decoded ?: [];
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
            // 'Assistant Restaurant Manager',
            // 'F&B Supervisor'
        ]);
    }
}
