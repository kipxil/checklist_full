<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenueTarget extends Model
{
    //
    protected $fillable = [
        'restaurant_id',
        'year',
        'month',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
