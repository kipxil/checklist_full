<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
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
            ->where('status', '!=', 'draft') // Hanya hitung yang submitted/approved (Opsional: hapus jika draft mau dihitung)
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
        ));
    }
}
