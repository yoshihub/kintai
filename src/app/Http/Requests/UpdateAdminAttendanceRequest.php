<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class UpdateAdminAttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'breaks.*.break_start' => 'nullable|date_format:H:i',
            'breaks.*.break_end' => 'nullable|date_format:H:i',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $this->validateTimeOrder($validator);
            $this->validateBreakTimes($validator);
            $this->validateNote($validator);
        });
    }

    protected function validateTimeOrder($validator)
    {
        $startTime = $this->input('start_time');
        $endTime = $this->input('end_time');

        if ($startTime && $endTime && $startTime >= $endTime) {
            $validator->errors()->add('time_order', '出勤時間もしくは退勤時間が不適切な値です。');
        }
    }

    protected function validateBreakTimes($validator)
    {
        $startTime = $this->input('start_time');
        $endTime = $this->input('end_time');
        $breaks = $this->input('breaks', []);

        $hasBreakTimeError = false;

        foreach ($breaks as $break) {
            $breakStart = $break['break_start'] ?? null;
            $breakEnd = $break['break_end'] ?? null;

            // 休憩開始時間のチェック
            if ($breakStart) {
                if (($startTime && $breakStart < $startTime) ||
                    ($endTime && $breakStart >= $endTime) ||
                    ($breakEnd && $breakStart >= $breakEnd)
                ) {
                    $hasBreakTimeError = true;
                    break;
                }
            }

            // 休憩終了時間のチェック
            if ($breakEnd) {
                if (($startTime && $breakEnd <= $startTime) ||
                    ($endTime && $breakEnd > $endTime)
                ) {
                    $hasBreakTimeError = true;
                    break;
                }
            }
        }

        if ($hasBreakTimeError) {
            $validator->errors()->add('break_time', '休憩時間が勤務時間外です。');
        }
    }

    protected function validateNote($validator)
    {
        $note = $this->input('note');

        if (empty(trim($note))) {
            $validator->errors()->add('note', '備考を記入してください。');
        }
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
