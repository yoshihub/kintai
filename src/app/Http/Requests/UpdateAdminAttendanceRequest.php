<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdateAdminAttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $endTime = $this->input('end_time');
                    if ($endTime && $value >= $endTime) {
                        $fail('出勤時間もしくは退勤時間が不適切な値です');
                    }
                }
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    // 休憩時間との比較チェック
                    if ($this->has('breaks')) {
                        foreach ($this->input('breaks') as $breakData) {
                            if (!empty($breakData['break_start']) && $breakData['break_start'] >= $value) {
                                $fail('出勤時間もしくは退勤時間が不適切な値です');
                            }
                            if (!empty($breakData['break_end']) && $breakData['break_end'] >= $value) {
                                $fail('出勤時間もしくは退勤時間が不適切な値です');
                            }
                        }
                    }
                }
            ],
            'breaks.*.break_start' => 'nullable|date_format:H:i',
            'breaks.*.break_end' => 'nullable|date_format:H:i',
            'note' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (empty(trim($value))) {
                        $fail('備考を記入してください');
                    }
                }
            ],
        ];
    }

    public function messages()
    {
        return [
            'start_time.required' => '出勤時間を入力してください',
            'start_time.date_format' => '出勤時間の形式が正しくありません',
            'end_time.required' => '退勤時間を入力してください',
            'end_time.date_format' => '退勤時間の形式が正しくありません',
            'breaks.*.break_start.date_format' => '休憩開始時間の形式が正しくありません',
            'breaks.*.break_end.date_format' => '休憩終了時間の形式が正しくありません',
        ];
    }
}
