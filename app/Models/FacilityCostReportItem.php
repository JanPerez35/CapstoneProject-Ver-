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
        'event_group_id',
        'is_group_parent',
        'sub_event_type',
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
        'parent_deducted_cost',
        'custom_parent_item_id',
    ];

    protected $casts = [
        'event_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'services' => 'array',
        'is_group_parent' => 'boolean',
    ];

    public function report()
    {
        return $this->belongsTo(FacilityCostReport::class, 'facility_cost_report_id');
    }

    public function facilityCost()
    {
        return $this->belongsTo(FacilityCost::class);
    }

    public function groupItems()
    {
        return $this->hasMany(self::class, 'event_group_id', 'event_group_id');
    }
}
