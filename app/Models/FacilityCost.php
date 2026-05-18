<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class FacilityCost
 *
 * Represents a configurable facility area inside the system.
 *
 * Stores the area name, measurement, service costs, and daily, weekly,
 * and monthly rates for each supported period type. These values are used
 * to calculate estimated operational costs for facility usage events.
 */
class FacilityCost extends Model
{
    use HasFactory;

    /**
     * Attributes that can be mass assigned.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'classroom_name',
        'pending_deletion',
        'supply_cost',
        'electricity_cost',
        'water_cost',
        'classroom_space',
        'daily_cost_1',
        'daily_cost_2',
        'weekly_cost_1',
        'weekly_cost_2',
        'monthly_cost_1',
        'monthly_cost_2',
        'daily_cost_3',
        'weekly_cost_3',
        'monthly_cost_3',
    ];

    /**
     * Gets all report items that used this facility area configuration.
     *
     * @return HasMany<FacilityCostReportItem>
     */
    public function reportItems(): HasMany
    {
        return $this->hasMany(FacilityCostReportItem::class);
    }
}