<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DailyReport extends Model
{
    //
    protected $fillable = [
        'restaurant_id',
        'user_id',
        'date',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('restaurant_scope', function (Builder $builder) {
            $user = Auth::user();

            // Logika Baru:
            // 1. User login & bukan Super Admin
            if ($user && !$user->hasRole('Super Admin')) {

                // Ambil semua ID restoran yang dipegang user
                // pluck('id') akan menghasilkan array [1, 2, 5]
                $myRestaurantIds = $user->restaurants->pluck('id')->toArray();

                // Gunakan WHERE IN
                $builder->whereIn('restaurant_id', $myRestaurantIds);
            }
        });
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function details()
    {
        return $this->hasMany(DailyReportDetail::class);
    }
}
