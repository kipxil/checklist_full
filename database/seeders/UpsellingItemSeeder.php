<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use App\Models\UpsellingItem;

class UpsellingItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // 1. Ambil Data Restoran (Pastikan ID-nya benar)
        $r209 = Restaurant::where('code', '209')->first();
        $njr  = Restaurant::where('code', 'NJR')->first(); // Pastikan kodenya njr (atau NJR sesuai db anda)
        $xfh  = Restaurant::where('code', 'XFH')->first();

        $items = [];

        // --- MENU UNTUK 209 DINING ---
        if ($r209) {
            $items[] = ['restaurant_id' => $r209->id, 'type' => 'food', 'name' => 'Wagyu Beef A5 (200gr)'];
            $items[] = ['restaurant_id' => $r209->id, 'type' => 'food', 'name' => 'Lobster Thermidor'];
            $items[] = ['restaurant_id' => $r209->id, 'type' => 'food', 'name' => 'Tomahawk Steak'];
            $items[] = ['restaurant_id' => $r209->id, 'type' => 'beverage', 'name' => 'Red Wine (Bottle)'];
            $items[] = ['restaurant_id' => $r209->id, 'type' => 'beverage', 'name' => 'Sparkling Water (Equil)'];
            $items[] = ['restaurant_id' => $r209->id, 'type' => 'beverage', 'name' => 'Signature Cocktail'];
        }

        // --- MENU UNTUK NAGANO ---
        if ($njr) {
            $items[] = ['restaurant_id' => $njr->id, 'type' => 'food', 'name' => 'Kobe Beef Set'];
            $items[] = ['restaurant_id' => $njr->id, 'type' => 'food', 'name' => 'Premium Sushi Platter'];
            $items[] = ['restaurant_id' => $njr->id, 'type' => 'food', 'name' => 'Salmon Sashimi (1kg)'];
            $items[] = ['restaurant_id' => $njr->id, 'type' => 'beverage', 'name' => 'Sake (Bottle)'];
            $items[] = ['restaurant_id' => $njr->id, 'type' => 'beverage', 'name' => 'Ocha Premium'];
        }

        // --- MENU UNTUK XIANG FU HAI ---
        if ($xfh) {
            $items[] = ['restaurant_id' => $xfh->id, 'type' => 'food', 'name' => 'Peking Duck (Whole)'];
            $items[] = ['restaurant_id' => $xfh->id, 'type' => 'food', 'name' => 'Abalone Braised'];
            $items[] = ['restaurant_id' => $xfh->id, 'type' => 'beverage', 'name' => 'Chinese Tea (Pot)'];
        }

        // Eksekusi Insert
        foreach ($items as $item) {
            UpsellingItem::firstOrCreate([
                'restaurant_id' => $item['restaurant_id'],
                'name' => $item['name']
            ], [
                'type' => $item['type']
            ]);
        }
    }
}
