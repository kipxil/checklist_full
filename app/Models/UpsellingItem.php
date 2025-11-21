<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UpsellingItem extends Model
{
    //
    protected $fillable = [
        'restaurant_id',
        'type',
        'name',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('restaurant_scope', function (Builder $builder) {
            $user = Auth::user();

            // Logika:
            // 1. User harus login
            // 2. User punya restaurant_id (bukan NULL)
            // 3. User BUKAN Super Admin (Super Admin boleh lihat semua)
            if ($user && $user->restaurant_id && !$user->hasRole('Super Admin')) {
                $builder->where('restaurant_id', $user->restaurant_id);
            }
        });
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
