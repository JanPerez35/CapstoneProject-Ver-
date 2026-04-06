<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Lending extends Model
{
   protected $table = 'lendings'; 
        protected $fillable = [
            'user_id',
            'commentary',
            'special_reason',
            'start_time',
            'end_time',
            'flag',
            'status',
        ];
    
    public function items()
    {
        return $this->hasMany(LendingItem::class, 'lending_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}