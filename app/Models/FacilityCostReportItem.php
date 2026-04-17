<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FacilityCostReportItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_cost_report_id',
        'facility_cost_id',
        'responsible',
        'period_type',
        'services',
        'rate_mode',
        'start_time',
        'end_time',
        'event_date',
        'end_date',
        'event_description',
        'hours_used',
        'calculated_cost',
    ];

    protected $casts = [
        'event_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'services' => 'array',
    ];

    public function report()
    {
        return $this->belongsTo(FacilityCostReport::class, 'facility_cost_report_id');
    }

    public function facilityCost()
    {
        return $this->belongsTo(FacilityCost::class);
    }
}
