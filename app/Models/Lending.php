<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lending extends Model
{
   protected $table = 'lendings'; 
        protected $fillable = [
            'user_id',
            'commentary',
            'start_time',
            'end_time',
            'flag',
            'status',
        ];
}