<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FacilityCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'classroom_name',
        'supply_cost',
        'electricity_cost',
        'water_cost',
        'lending_certificate_1',
        'lending_certificate_2',
        'lending_certificate_3',
        'classroom_space',
        'daily_cost_1',
        'daily_cost_2',
        'weekly_cost_1',
        'weekly_cost_2',
        'monthly_cost_1',
        'monthly_cost_2',
    ];

    public function reportItems()
    {
        return $this->hasMany(FacilityCostReportItem::class);
    }
}