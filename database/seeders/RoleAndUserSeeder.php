<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $roles = [
            'Super Admin',
            'Restaurant Manager',
            'Assistant Restaurant Manager',
            'F&B Supervisor',
            'Waiter',
            'Cashier',
            'Bartender',
            'Daily Worker',
            'Trainee',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        $r209 = Restaurant::where('code', '209')->first();
        $ngn  = Restaurant::where('code', 'NJR')->first();

        $password = Hash::make('password');

        $admin = User::firstOrCreate([
            'nik' => '0000', // Username login
        ], [
            'name' => 'Super Admin',
            'password' => $password,
            'restaurant_id' => null, // Tidak terikat
        ]);
        $admin->assignRole('Super Admin');

        if ($r209) {
            $manager209 = User::firstOrCreate([
                'nik' => '1000',
            ], [
                'name' => 'Test Manager 209',
                'password' => $password,
                'restaurant_id' => $r209->id,
            ]);
            $manager209->assignRole('Restaurant Manager');

            // C. WAITER 209 (Requester Group)
            $waiter209 = User::firstOrCreate([
                'nik' => '1005',
            ], [
                'name' => 'Test Waiter 209',
                'password' => $password,
                'restaurant_id' => $r209->id,
            ]);
            $waiter209->assignRole('Waiter');
        }

        // D. MANAGER NAGANO (Untuk tes isolasi data)
        if ($ngn) {
            $managerNgn = User::firstOrCreate([
                'nik' => '2000',
            ], [
                'name' => 'Test Manager Nagano',
                'password' => $password,
                'restaurant_id' => $ngn->id,
            ]);
            $managerNgn->assignRole('Restaurant Manager');
        }
    }
}
