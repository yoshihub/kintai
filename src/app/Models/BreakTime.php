<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    protected $table = 'breaks';

    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end'
    ];

    protected $casts = [
        'break_start' => 'datetime:H:i:s',
        'break_end' => 'datetime:H:i:s',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
