<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Daftar Roles
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

        // 2. Ambil Data Restoran
        // Pastikan kode ini sesuai dengan yang ada di RestaurantSeeder
        $r209 = Restaurant::where('code', '209')->first();
        $ngn  = Restaurant::where('code', 'NJR')->first(); // Sesuaikan jika di DB anda kodenya 'NJR'

        // Password default
        $password = Hash::make('password');

        // --- 3. Buat Dummy Users ---

        // A. SUPER ADMIN (Global Access)
        // Tidak perlu di-sync ke restoran manapun
        $admin = User::firstOrCreate([
            'nik' => '0000',
        ], [
            'name' => 'Super Admin',
            'password' => $password,
        ]);
        $admin->assignRole('Super Admin');


        // B. MANAGER 209 (Single Unit)
        if ($r209) {
            $manager209 = User::firstOrCreate([
                'nik' => '1000',
            ], [
                'name' => 'Manager 209',
                'password' => $password,
            ]);
            $manager209->assignRole('Restaurant Manager');

            // HUBUNGKAN KE RESTORAN (LOGIC BARU)
            $manager209->restaurants()->sync([$r209->id]);


            // C. WAITER 209
            $waiter209 = User::firstOrCreate([
                'nik' => '1005',
            ], [
                'name' => 'Waiter 209',
                'password' => $password,
            ]);
            $waiter209->assignRole('Waiter');

            // HUBUNGKAN KE RESTORAN
            $waiter209->restaurants()->sync([$r209->id]);
        }


        // D. MANAGER NAGANO
        if ($ngn) {
            $managerNgn = User::firstOrCreate([
                'nik' => '2000',
            ], [
                'name' => 'Manager Nagano',
                'password' => $password,
            ]);
            $managerNgn->assignRole('Restaurant Manager');

            // HUBUNGKAN KE RESTORAN
            $managerNgn->restaurants()->sync([$ngn->id]);
        }


        // E. CONTOH AREA MANAGER (CLUSTER - 2 RESTORAN)
        // User ini memegang 209 DAN Nagano sekaligus
        if ($r209 && $ngn) {
            $areaMgr = User::firstOrCreate([
                'nik' => '3000',
            ], [
                'name' => 'Area Manager (Cluster)',
                'password' => $password,
            ]);
            $areaMgr->assignRole('Restaurant Manager'); // Role tetap Manager

            // HUBUNGKAN KE BANYAK RESTORAN
            $areaMgr->restaurants()->sync([$r209->id, $ngn->id]);
        }
    }
}
