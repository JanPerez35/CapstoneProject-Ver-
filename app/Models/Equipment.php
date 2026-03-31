<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $table = 'equipment';

    protected $fillable = [
        'category',
        'quantity',
        'available_quantity',
        'description',
        'location',
        'equipment_photo_url',
        'stats',
    ];

    public function lendingItems(){

    return $this->hasMany(LendingItem::class, 'equipment_id');
    
    }
}