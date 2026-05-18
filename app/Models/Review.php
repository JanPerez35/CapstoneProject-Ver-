<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Review
 *
 * Represents a marketplace review made by one user about a seller
 * in relation to a specific post.
 *
 * A review stores the reviewer, seller, reviewed post, rating value,
 * optional comment, and review status. In MAIKINE, low-rating reviews
 * can also support the marketplace report flow.
 */
class Review extends Model
{
    /**
     * Attributes that can be mass assigned.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'post_id',
        'user_id',
        'seller_id',
        'rating',
        'comment',
        'status',
    ];

    /**
     * Gets the user who submitted the review.
     *
     * @return BelongsTo<User, Review>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Gets the seller who received the review.
     *
     * @return BelongsTo<User, Review>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Gets the marketplace post associated with the review.
     *
     * @return BelongsTo<Post, Review>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}