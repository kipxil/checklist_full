<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\UpsellingItem;
use Illuminate\Http\Request;

class UpsellingItemController extends Controller
{
    //
    public function index(Request $request)
    {
        // Mulai Query
        $query = UpsellingItem::with('restaurant'); // Eager load relasi agar hemat query

        // Cek apakah ada request filter restoran
        if ($request->has('restaurant_id') && $request->restaurant_id != '') {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        // Ambil data (Paginate 20 item per halaman)
        $items = $query->orderBy('restaurant_id')->orderBy('type')->paginate(20);

        // Ambil daftar restoran untuk isi dropdown filter
        $restaurants = Restaurant::all();

        return view('upselling-items.index', compact('items', 'restaurants'));
    }

    public function create()
    {
        $restaurants = Restaurant::all();
        return view('upselling-items.create', compact('restaurants'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'type' => 'required|in:food,beverage',
            'name' => 'required|string|max:255',
        ]);

        UpsellingItem::create($request->all());

        return redirect()->route('upselling-items.index')
            ->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit(UpsellingItem $upsellingItem)
    {
        $restaurants = Restaurant::all();
        return view('upselling-items.edit', compact('upsellingItem', 'restaurants'));
    }

    public function update(Request $request, UpsellingItem $upsellingItem)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'type' => 'required|in:food,beverage',
            'name' => 'required|string|max:255',
        ]);

        $upsellingItem->update($request->all());

        return redirect()->route('upselling-items.index')
            ->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(UpsellingItem $upsellingItem)
    {
        $upsellingItem->delete();

        return back()->with('success', 'Menu berhasil dihapus.');
    }
}
