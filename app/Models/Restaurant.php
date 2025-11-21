<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    //
    protected $fillable = [
        'code',
        'name',
    ];

    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class);
    }

    public function upsellingItems()
    {
        return $this->hasMany(UpsellingItem::class);
    }
}
