<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LendingItem extends Model
{
    protected $table = 'lending_items';

    protected $fillable = [
        'lending_id',
        'equipment_id',
        'quantity',
        'item_status',
    ];

    public function lending()
    {
        return $this->belongsTo(Lending::class, 'lending_id');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }
}