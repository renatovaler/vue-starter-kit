<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BcbData extends Model
{
    protected $fillable = [
        'bcb_code',
        'data',
        'valor',
    ];

    protected $dates = ['data'];
}
