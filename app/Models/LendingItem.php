<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class LendingItem
 *
 * Represents an individual equipment item inside a lending request.
 *
 * Each lending item stores the requested equipment, requested quantity,
 * and item-level status. It belongs to one parent Lending request and
 * one Equipment record.
 */
class LendingItem extends Model
{
    /**
     * Database table associated with the model.
     *
     * @var string
     */
    protected $table = 'lending_items';

    /**
     * Attributes that can be mass assigned.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lending_id',
        'equipment_id',
        'quantity',
        'item_status',
    ];

    /**
     * Gets the parent lending request for this item.
     *
     * @return BelongsTo<Lending, LendingItem>
     */
    public function lending(): BelongsTo
    {
        return $this->belongsTo(Lending::class, 'lending_id');
    }

    /**
     * Gets the equipment record requested in this lending item.
     *
     * @return BelongsTo<Equipment, LendingItem>
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }
}