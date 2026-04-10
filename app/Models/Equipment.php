<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use SoftDeletes;

    protected $table = 'equipment';

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

    public function lendingItems()
    {
        return $this->hasMany(LendingItem::class, 'equipment_id');
    }
}