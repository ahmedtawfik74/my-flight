<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    protected $fillable = [
        'origin_city', 'destination_city', 'price','takeoff_time','landing_time','date_diff_in_min'
    ];
    // protected $casts = [
    //     'takeoff_time' => 'datetime',
    //     'landing_time' => 'datetime',
    // ];
}
