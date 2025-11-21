<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReportDetail extends Model
{
    //
    protected $fillable = [
        'daily_report_id',
        'session_type',
        'revenue_food',
        'revenue_beverage',
        'revenue_others',
        'revenue_event',
        'cover_data',
        'upselling_data',
        'competitor_data',
        'additional_data',
        'thematic',
        'staff_on_duty',
        'remarks',
        'vip_remarks',
    ];

    protected $casts = [
        'cover_data' => 'array',
        'upselling_data' => 'array',
        'competitor_data' => 'array',
        'additional_data' => 'array',
        'vip_remarks' => 'array',
        'staff_on_duty' => 'array',
        'revenue_food' => 'decimal:2',
        'revenue_beverage' => 'decimal:2',
        'revenue_others' => 'decimal:2',
        'revenue_event' => 'decimal:2',
    ];

    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class);
    }
}
