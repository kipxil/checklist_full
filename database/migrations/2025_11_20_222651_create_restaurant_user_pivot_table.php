<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('restaurant_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Mencegah duplikasi data (User A pegang Resto 1 dua kali)
            $table->unique(['user_id', 'restaurant_id']);
        });

        $users = DB::table('users')->whereNotNull('restaurant_id')->get();

        foreach ($users as $user) {
            DB::table('restaurant_user')->insert([
                'user_id' => $user->id,
                'restaurant_id' => $user->restaurant_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            // Kita drop foreign key dulu sebelum drop kolom
            // Nama index biasanya: users_restaurant_id_foreign
            // (Sesuaikan jika error, tapi ini standar Laravel)
            $table->dropForeign(['restaurant_id']);
            $table->dropColumn('restaurant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Kembalikan Kolom Lama
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->nullable()->after('id')->constrained()->onDelete('set null');
        });

        // 2. Kembalikan Data (Ambil salah satu resto saja per user)
        $pivots = DB::table('restaurant_user')->get();
        foreach ($pivots as $pivot) {
            // Update user, ambil pivot pertama yang ketemu
            DB::table('users')
                ->where('id', $pivot->user_id)
                ->update(['restaurant_id' => $pivot->restaurant_id]);
        }

        // 3. Hapus Tabel Pivot
        Schema::dropIfExists('restaurant_user');
    }
};
