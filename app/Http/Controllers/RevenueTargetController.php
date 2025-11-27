<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\RevenueTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RevenueTargetController extends Controller
{
    //
    public function index(Request $request)
    {
        $user = Auth::user();

        // Filter Restoran (Scope User)
        $query = RevenueTarget::with('restaurant');

        if (!$user->hasRole('Super Admin')) {
            // Jika user punya akses terbatas, hanya tampilkan target milik restonya
            $query->whereIn('restaurant_id', $user->restaurants->pluck('id'));
        }

        // Filter Tahun (Default Tahun Ini)
        $year = $request->input('year', date('Y'));
        $query->where('year', $year);

        $targets = $query->orderBy('month', 'desc')->paginate(10);
        $restaurants = $user->hasRole('Super Admin') ? Restaurant::all() : $user->restaurants;

        return view('revenue-targets.index', compact('targets', 'restaurants', 'year'));
    }

    public function store(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'year' => 'required|integer',
            'amount' => 'required',
            // Bulan wajib diisi JIKA checkbox full year TIDAK dicentang
            'month' => 'required_without:is_full_year',
        ]);

        // 2. Proteksi Akses (Scope User)
        $user = Auth::user();
        if (!$user->hasRole('Super Admin')) {
            if (!$user->restaurants->contains($request->restaurant_id)) {
                abort(403, 'Unauthorized action.');
            }
        }

        // 3. Sanitasi Rupiah
        $amount = str_replace('.', '', $request->amount);

        // 4. Logika Simpan (Single vs Full Year)
        if ($request->has('is_full_year')) {
            // --- LOGIKA FULL YEAR (LOOPING) ---
            // Kita loop dari bulan 1 sampai 12
            foreach (range(1, 12) as $month) {
                RevenueTarget::updateOrCreate(
                    [
                        'restaurant_id' => $request->restaurant_id,
                        'year' => $request->year,
                        'month' => $month, // Gunakan index loop
                    ],
                    [
                        'amount' => $amount
                    ]
                );
            }
            $msg = 'Target revenue setahun penuh berhasil disimpan.';
        } else {
            // --- LOGIKA SINGLE MONTH (LAMA) ---
            RevenueTarget::updateOrCreate(
                [
                    'restaurant_id' => $request->restaurant_id,
                    'year' => $request->year,
                    'month' => $request->month, // Gunakan input user
                ],
                [
                    'amount' => $amount
                ]
            );
            $msg = 'Target revenue bulan terpilih berhasil disimpan.';
        }

        return back()->with('success', $msg);
    }
}
