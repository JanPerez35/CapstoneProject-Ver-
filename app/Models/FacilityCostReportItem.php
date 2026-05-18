<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class FacilityCostReportItem
 *
 * Represents one cost-calculated facility usage entry.
 *
 * A report item can be a main event, a related-area sub-event, or a
 * custom-day modification. Items may be grouped through event_group_id
 * so the system can display one real-world event with multiple related
 * cost entries and a combined total.
 */
class FacilityCostReportItem extends Model
{
    use HasFactory;

    /**
     * Attributes that can be mass assigned.
     *
     * @var array<int, string>
     */
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

    /**
     * Attribute casting rules.
     *
     * services is cast to an array so selected services can be handled
     * directly as PHP arrays after being stored as JSON.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'event_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'services' => 'array',
        'is_group_parent' => 'boolean',
    ];

    /**
     * Gets the parent facility cost report for this item.
     *
     * @return BelongsTo<FacilityCostReport, FacilityCostReportItem>
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(FacilityCostReport::class, 'facility_cost_report_id');
    }

    /**
     * Gets the facility area and rate configuration used by this item.
     *
     * @return BelongsTo<FacilityCost, FacilityCostReportItem>
     */
    public function facilityCost(): BelongsTo
    {
        return $this->belongsTo(FacilityCost::class);
    }

    /**
     * Gets all report items that belong to the same event group.
     *
     * This relationship is used to retrieve the parent event, related-area
     * sub-events, and custom-day modifications that share the same
     * event_group_id.
     *
     * @return HasMany<FacilityCostReportItem>
     */
    public function groupItems(): HasMany
    {
        return $this->hasMany(self::class, 'event_group_id', 'event_group_id');
    }
}