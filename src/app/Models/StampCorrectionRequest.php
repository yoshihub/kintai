<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $table = 'stamp_correction_requests';

    protected $fillable = [
        'user_id',
        'attendance_id',
        'start_time',
        'end_time',
        'breaks',
        'note',
        'status'
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'breaks' => 'array',
    ];

    // ユーザーとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 勤怠レコードとのリレーション
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
