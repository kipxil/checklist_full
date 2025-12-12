<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    //
    public function index()
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        // ---------------------------------------------------------
        // 1. QUERY DASAR (Otomatis terfilter oleh Global Scope)
        // ---------------------------------------------------------

        // Widget 1: Waiting Approval (Status Submitted)
        $waitingApproval = DailyReport::where('status', 'submitted')->count();

        // Widget 2: Drafts (Status Draft)
        $myDrafts = DailyReport::where('status', 'draft')
            ->where('user_id', $user->id) // Draft spesifik milik user login (opsional)
            ->count();

        // Widget 3: Today's Revenue
        // Kita ambil laporan hari ini beserta detail-nya
        $todaysReports = DailyReport::whereDate('date', $today)
            ->where('status', 'approved') // <--- FILTER: HANYA APPROVED
            ->with('details')
            ->get();

        $todayRevenue = 0;
        foreach ($todaysReports as $report) {
            foreach ($report->details as $detail) {
                // Jumlahkan semua komponen revenue (Food + Bev + Others + Event)
                $totalSesi = $detail->revenue_food
                    + $detail->revenue_beverage
                    + $detail->revenue_others
                    + $detail->revenue_event;
                $todayRevenue += $totalSesi;
            }
        }

        $chartData = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartData->put($date, 0);
        }

        // 2. Ambil Data dari Database (Otomatis terfilter Scope User/Resto)
        $weeklyReports = DailyReport::whereDate('date', '>=', now()->subDays(6))
            ->where('status', 'approved') // <--- FILTER: HANYA APPROVED
            ->with('details')
            ->get();

        // 3. Isi Kerangka Array dengan Data Asli
        foreach ($weeklyReports as $report) {
            $dateKey = $report->date->format('Y-m-d');

            // Hitung total per laporan
            $reportTotal = 0;
            foreach ($report->details as $detail) {
                $reportTotal += $detail->revenue_food
                    + $detail->revenue_beverage
                    + $detail->revenue_others
                    + $detail->revenue_event;
            }

            // Tambahkan ke tanggal yang sesuai (Accumulate jika ada banyak resto)
            if ($chartData->has($dateKey)) {
                $chartData[$dateKey] += $reportTotal;
            }
        }

        // 4. Pisahkan Keys (Tanggal) dan Values (Uang) untuk ApexCharts
        // Format tanggal dipercantik jadi "21 Nov"
        $chartLabels = $chartData->keys()->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->values();
        $chartValues = $chartData->values();

        // 1. Siapkan Kerangka Array untuk 4 Series
        $compData = [
            'us' => clone $chartData,        // Pakai clone agar index tanggalnya sama
            'shangrila' => clone $chartData,
            'jw' => clone $chartData,
            'sheraton' => clone $chartData,
        ];

        // Reset values jadi 0 semua
        foreach ($compData as $key => $collection) {
            $compData[$key] = $collection->map(fn() => 0);
        }

        // 2. Loop Data Laporan (Pakai variabel $weeklyReports yg sudah ada)
        foreach ($weeklyReports as $report) {
            $dateKey = $report->date->format('Y-m-d');

            foreach ($report->details as $detail) {

                // A. HITUNG TOTAL COVER KITA SENDIRI (SUM JSON)
                $myTotalCover = 0;
                if (!empty($detail->cover_data) && is_array($detail->cover_data)) {
                    foreach ($detail->cover_data as $val) {
                        // Jumlahkan hanya jika nilainya angka
                        if (is_numeric($val)) {
                            $myTotalCover += $val;
                        }
                    }
                }

                // B. AMBIL DATA KOMPETITOR
                $shangrila = $detail->competitor_data['shangrila_cover'] ?? 0;
                $jw = $detail->competitor_data['jw_marriott_cover'] ?? 0;
                $sheraton = $detail->competitor_data['sheraton_cover'] ?? 0;

                // C. MASUKKAN KE ARRAY (Accumulate)
                if ($compData['us']->has($dateKey)) {
                    $compData['us'][$dateKey] += $myTotalCover;
                    $compData['shangrila'][$dateKey] += (int)$shangrila;
                    $compData['jw'][$dateKey] += (int)$jw;
                    $compData['sheraton'][$dateKey] += (int)$sheraton;
                }
            }
        }

        // 3. Format Data untuk Grafik
        $compSeries = [
            ['name' => 'Our Restaurant', 'data' => $compData['us']->values()],
            ['name' => 'Shangri-La', 'data' => $compData['shangrila']->values()],
            ['name' => 'JW Marriott', 'data' => $compData['jw']->values()],
            ['name' => 'Sheraton', 'data' => $compData['sheraton']->values()],
        ];

        $currentMonth = now()->month;
        $currentYear = now()->year;

        // 1. Hitung Actual Revenue Bulan Ini (MTD)
        // Filter berdasarkan bulan & tahun ini
        $mtdReports = DailyReport::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->where('status', 'approved') // <--- FILTER: HANYA APPROVED
            ->with('details')
            ->get();

        $mtdRevenue = 0;
        foreach ($mtdReports as $report) {
            foreach ($report->details as $detail) {
                $mtdRevenue += $detail->revenue_food
                    + $detail->revenue_beverage
                    + $detail->revenue_others
                    + $detail->revenue_event;
            }
        }

        // 2. Ambil Target Revenue Bulan Ini
        $targetQuery = \App\Models\RevenueTarget::where('month', $currentMonth)
            ->where('year', $currentYear);

        // Manual Scope: Jika bukan Super Admin, filter target milik restonya saja
        if (!$user->hasRole('Super Admin')) {
            $targetQuery->whereIn('restaurant_id', $user->restaurants->pluck('id'));
        }

        $monthlyTarget = $targetQuery->sum('amount');

        // 3. Hitung Persentase (Cegah division by zero)
        $achievementPercent = $monthlyTarget > 0 ? ($mtdRevenue / $monthlyTarget) * 100 : 0;

        $breakdownPerformance = [];
        $relevantRestaurants = collect();

        // 1. Tentukan Restoran mana yang mau dihitung
        if ($user->hasRole('Super Admin')) {
            $relevantRestaurants = Restaurant::all();
        } else {
            // Untuk Cluster & Single Unit, ambil dari relasi
            $relevantRestaurants = $user->restaurants;
        }

        // 2. Loop setiap restoran untuk hitung target vs actual
        // Kita hitung manual di sini agar akurat per ID restoran
        foreach ($relevantRestaurants as $resto) {

            // A. Hitung Actual MTD (Approved Only) untuk Resto ini
            $restoReports = DailyReport::where('restaurant_id', $resto->id)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->where('status', 'approved')
                ->with('details') // Load detail agar bisa sum revenue
                ->get();

            $actual = 0;
            foreach ($restoReports as $rpt) {
                foreach ($rpt->details as $dtl) {
                    $actual += $dtl->revenue_food + $dtl->revenue_beverage + $dtl->revenue_others + $dtl->revenue_event;
                }
            }

            // B. Ambil Target Bulan Ini untuk Resto ini
            $target = \App\Models\RevenueTarget::where('restaurant_id', $resto->id)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->sum('amount');

            // C. Hitung Persentase
            $percentage = $target > 0 ? ($actual / $target) * 100 : 0;

            // D. Masukkan ke Array
            $breakdownPerformance[] = [
                'id' => $resto->id,
                'name' => $resto->name,
                'code' => $resto->code,
                'target' => $target,
                'actual' => $actual,
                'percentage' => $percentage
            ];
        }

        // ---------------------------------------------------------
        // 2. TABEL RINGKASAN (5 Laporan Terakhir)
        // ---------------------------------------------------------
        $recentReports = DailyReport::with(['restaurant', 'user'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Kirim semua data ke View
        return view('dashboard', compact(
            'waitingApproval',
            'myDrafts',
            'todayRevenue',
            'recentReports',
            'chartLabels',
            'chartValues',
            'compSeries',
            'mtdRevenue',
            'monthlyTarget',
            'achievementPercent',
            'breakdownPerformance',
        ));
    }

    public function getOutletAnalytics(Request $request, Restaurant $restaurant)
    {
        // 1. Validasi Akses (Security)
        $user = Auth::user();
        if (!$user->hasRole('Super Admin') && !$user->restaurants->contains($restaurant->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // 2. Ambil Filter Tanggal (Default: Bulan Ini)
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        // 3. Query Data (Hanya Approved)
        $reports = DailyReport::where('restaurant_id', $restaurant->id)
            ->where('status', 'approved') // Wajib Approved
            ->whereBetween('date', [$startDate, $endDate])
            ->with('details')
            ->get();

        // 4. Inisialisasi Struktur Data Matriks
        // Kita butuh array kosong untuk menampung penjumlahan
        $sessions = ['breakfast', 'lunch', 'dinner', 'supper'];

        // Structure: ['Item Name' => ['breakfast' => 0, 'lunch' => 0, ...]]
        $revenueMatrix = [
            'Food Revenue' => array_fill_keys($sessions, 0),
            'Beverage Revenue' => array_fill_keys($sessions, 0),
            'Others Revenue' => array_fill_keys($sessions, 0),
            'Event Revenue' => array_fill_keys($sessions, 0),
        ];

        $coverMatrix = []; // Dinamis (tergantung key yang ditemukan)
        $competitorMatrix = []; // Dinamis
        $usCoverTotal = array_fill_keys($sessions, 0); // Untuk baris "Us" di tabel kompetitor

        // 5. Loop & Agregasi Data (The Heavy Lifting)
        foreach ($reports as $report) {
            foreach ($report->details as $detail) {
                $sess = $detail->session_type;

                // A. Agregasi Revenue
                $revenueMatrix['Food Revenue'][$sess] += $detail->revenue_food;
                $revenueMatrix['Beverage Revenue'][$sess] += $detail->revenue_beverage;
                $revenueMatrix['Others Revenue'][$sess] += $detail->revenue_others;
                $revenueMatrix['Event Revenue'][$sess] += $detail->revenue_event;

                // B. Agregasi Cover (Dinamis)
                if (!empty($detail->cover_data) && is_array($detail->cover_data)) {
                    foreach ($detail->cover_data as $key => $val) {
                        if (is_numeric($val)) {
                            // Bersihkan nama key (misal: "in_house_adult" -> "In House Adult")
                            $cleanKey = ucwords(str_replace('_', ' ', $key));

                            // Init jika belum ada di matriks
                            if (!isset($coverMatrix[$cleanKey])) {
                                $coverMatrix[$cleanKey] = array_fill_keys($sessions, 0);
                            }

                            $coverMatrix[$cleanKey][$sess] += $val;
                            $usCoverTotal[$sess] += $val; // Tambah ke total kita
                        }
                    }
                }

                // C. Agregasi Competitor
                if (!empty($detail->competitor_data) && is_array($detail->competitor_data)) {
                    foreach ($detail->competitor_data as $key => $val) {
                        if (is_numeric($val)) {
                            $cleanKey = ucwords(str_replace(['_cover', 'cover', '_'], ['', '', ' '], $key)); // Hapus kata "cover" agar pendek

                            if (!isset($competitorMatrix[$cleanKey])) {
                                $competitorMatrix[$cleanKey] = array_fill_keys($sessions, 0);
                            }

                            $competitorMatrix[$cleanKey][$sess] += $val;
                        }
                    }
                }
            }
        }

        // 6. Masukkan "Us (Our Resto)" ke baris pertama Competitor Matrix
        // Kita merge array agar "Us" ada di paling atas
        $competitorMatrix = array_merge(
            ['Us (' . $restaurant->name . ')' => $usCoverTotal],
            $competitorMatrix
        );

        // 7A. Siapkan Data untuk Grafik Cover Report
        $chartCategories = array_keys($coverMatrix); // Label Sumbu X
        $chartSeries = [];

        foreach ($sessions as $sess) {
            $dataPerSession = [];
            foreach ($chartCategories as $category) {
                // Ambil data dari matrix, default 0 jika error
                $dataPerSession[] = $coverMatrix[$category][$sess] ?? 0;
            }

            $chartSeries[] = [
                'name' => ucfirst($sess), // Breakfast, Lunch, etc
                'data' => $dataPerSession
            ];
        }

        // 7B. SIAPKAN DATA REVENUE CHART
        // Categories: ['Food Revenue', 'Beverage Revenue', 'Others Revenue', 'Event Revenue']
        $revChartCategories = array_keys($revenueMatrix);
        $revChartSeries = [];

        foreach ($sessions as $sess) {
            $dataPerSession = [];
            foreach ($revChartCategories as $category) {
                // Ambil data dari matrix
                $dataPerSession[] = $revenueMatrix[$category][$sess] ?? 0;
            }

            $revChartSeries[] = [
                'name' => ucfirst($sess), // Breakfast, Lunch, etc
                'data' => $dataPerSession
            ];
        }

        // 7C. SIAPKAN DATA COMPETITOR CHART
        // Categories: ['Us (Restaurant Name)', 'Shangri-La', 'JW Marriott', ...]
        $compChartCategories = array_keys($competitorMatrix);
        $compChartSeries = [];

        foreach ($sessions as $sess) {
            $dataPerSession = [];
            foreach ($compChartCategories as $hotel) {
                $dataPerSession[] = $competitorMatrix[$hotel][$sess] ?? 0;
            }

            $compChartSeries[] = [
                'name' => ucfirst($sess),
                'data' => $dataPerSession
            ];
        }

        // 8. Return Partial View
        // Kita kirim data yang sudah matang ke view khusus (belum kita buat)
        return view('analytics_modal', compact(
            'restaurant',
            'startDate',
            'endDate',
            'sessions',
            'revenueMatrix',
            'coverMatrix',
            'competitorMatrix',
            'chartCategories',
            'chartSeries',
            'revChartCategories',
            'revChartSeries',
            'compChartCategories',
            'compChartSeries',
        ));
    }
}
