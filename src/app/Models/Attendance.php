<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status'
    ];

    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }
}
