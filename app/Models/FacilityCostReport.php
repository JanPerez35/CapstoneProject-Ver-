<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class FacilityCostReport
 *
 * Represents a facility cost report generated or owned by a user.
 *
 * A report groups multiple FacilityCostReportItem records, where each item
 * represents an event, related area, or custom-day modification used to
 * calculate estimated operational costs.
 */
class FacilityCostReport extends Model
{
    use HasFactory;

    /**
     * Attributes that can be mass assigned.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
    ];

    /**
     * Gets the user associated with this facility cost report.
     *
     * @return BelongsTo<User, FacilityCostReport>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Gets all report items included in this facility cost report.
     *
     * @return HasMany<FacilityCostReportItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(FacilityCostReportItem::class);
    }
}