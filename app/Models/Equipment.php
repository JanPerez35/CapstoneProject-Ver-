<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Equipment
 *
 * Represents an inventory equipment item available through Kinventory.
 *
 * Stores the equipment category, total quantity, available quantity,
 * deletion status, description, location, photo path, and optional stats.
 * Soft deletes are used so equipment records can be hidden without
 * immediately removing historical lending relationships.
 */
class Equipment extends Model
{
    use SoftDeletes;

    /**
     * Database table associated with the model.
     *
     * @var string
     */
    protected $table = 'equipment';

    /**
     * Attributes that can be mass assigned.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category',
        'quantity',
        'available_quantity',
        'pending_deletion',
        'description',
        'location',
        'equipment_photo_url',
        'stats',
    ];

    /**
     * Attribute casting rules.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'pending_deletion' => 'boolean',
    ];

    /**
     * Gets all lending item records associated with this equipment.
     *
     * @return HasMany<LendingItem>
     */
    public function lendingItems(): HasMany
    {
        return $this->hasMany(LendingItem::class, 'equipment_id');
    }
}