<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    //
    public function index()
    {
        $restaurants = Restaurant::all();
        return view('restaurants.index', compact('restaurants'));
    }

    /**
     * Form Tambah.
     */
    public function create()
    {
        return view('restaurants.create');
    }

    /**
     * Simpan Data Baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:restaurants,code',
            'name' => 'required|string|max:255',
        ]);

        Restaurant::create($request->all());

        return redirect()->route('restaurants.index')
            ->with('success', 'Restoran berhasil ditambahkan.');
    }

    /**
     * Form Edit.
     */
    public function edit(Restaurant $restaurant)
    {
        return view('restaurants.edit', compact('restaurant'));
    }

    /**
     * Update Data.
     */
    public function update(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            // Validasi unique, tapi kecualikan ID restoran ini sendiri
            'code' => 'required|string|max:10|unique:restaurants,code,' . $restaurant->id,
            'name' => 'required|string|max:255',
        ]);

        $restaurant->update($request->all());

        return redirect()->route('restaurants.index')
            ->with('success', 'Data restoran berhasil diperbarui.');
    }

    /**
     * Hapus Data.
     */
    public function destroy(Restaurant $restaurant)
    {
        // Peringatan: Menghapus restoran akan menghapus semua laporan terkait (Cascade)
        $restaurant->delete();

        return back()->with('success', 'Restoran berhasil dihapus.');
    }
}
