<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Lending
 *
 * Represents an equipment lending request made by a user.
 *
 * A lending request can be a standard request or a special-case request.
 * Standard requests may be approved automatically, while special-case
 * requests remain pending for admin review.
 *
 * The model stores the requester, pickup and return time range, optional
 * admin/user commentary, special-case reason, request flag, and overall
 * lending status.
 */
class Lending extends Model
{
    /**
     * Database table associated with the model.
     *
     * @var string
     */
    protected $table = 'lendings';

    /**
     * Attributes that can be mass assigned.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'commentary',
        'special_reason',
        'start_time',
        'end_time',
        'flag',
        'status',
    ];

    /**
     * Gets all equipment items included in this lending request.
     *
     * @return HasMany<LendingItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(LendingItem::class, 'lending_id');
    }

    /**
     * Gets the user who created the lending request.
     *
     * @return BelongsTo<User, Lending>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}