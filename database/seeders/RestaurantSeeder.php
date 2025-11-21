<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Restaurant;

class RestaurantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $restaurants = [
            ['code' => '209', 'name' => '209 Dining'],
            ['code' => 'XFH', 'name' => 'Xiang Fu Hai'],
            ['code' => 'CHA', 'name' => 'Chamas'],
            ['code' => 'NJR', 'name' => 'Nagano Japanese Restaurant'],
            ['code' => 'VODA', 'name' => 'Voda Bistro'],
            ['code' => 'JM', 'name' => 'Joe Milano'],
        ];

        foreach ($restaurants as $restaurant) {
            Restaurant::firstOrCreate(
                ['code' => $restaurant['code']],
                ['name' => $restaurant['name']],
            );
        }
    }
}
