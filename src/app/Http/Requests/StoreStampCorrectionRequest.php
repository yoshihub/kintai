<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class StoreStampCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'attendance_id' => 'required|exists:attendances,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'breaks' => 'nullable|array',
            'breaks.*.break_start' => 'nullable|date_format:H:i',
            'breaks.*.break_end' => 'nullable|date_format:H:i',
            'note' => 'required|string',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'note.required' => '備考を記入してください',
        ];
    }

    /**
     * バリデーション後の追加チェック
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 出勤時間と退勤時間の比較
            if ($this->start_time && $this->end_time) {
                $startTime = Carbon::createFromFormat('H:i', $this->start_time);
                $endTime = Carbon::createFromFormat('H:i', $this->end_time);

                if ($startTime->gte($endTime)) {
                    $validator->errors()->add('start_time', '出勤時間もしくは退勤時間が不適切な値です');
                }
            }

            // 休憩時間のチェック
            if ($this->has('breaks') && $this->start_time && $this->end_time) {
                $startTime = Carbon::createFromFormat('H:i', $this->start_time);
                $endTime = Carbon::createFromFormat('H:i', $this->end_time);

                foreach ($this->breaks ?? [] as $key => $break) {
                    $hasError = false;

                    //休憩開始時間が出勤時間より前、または退勤時間より後ならエラー
                    if (!empty($break['break_start'])) {
                        $breakStart = Carbon::createFromFormat('H:i', $break['break_start']);
                        if ($breakStart->lt($startTime) || $breakStart->gt($endTime)) {
                            $validator->errors()->add("breaks.{$key}.break_start", '休憩時間が不適切な値です');
                            $hasError = true;
                        }
                    }

                    // 休憩終了時間が出勤時間より前、または退勤時間より後ならエラー
                    if (!$hasError && !empty($break['break_end'])) {
                        $breakEnd = Carbon::createFromFormat('H:i', $break['break_end']);
                        if ($breakEnd->lt($startTime) || $breakEnd->gt($endTime)) {
                            $validator->errors()->add("breaks.{$key}.break_end", '出勤時間もしくは退勤時間が不適切な値です');
                            $hasError = true;
                        }
                    }

                    // 休憩開始時間が休憩終了時間より後の場合のチェック
                    if (!$hasError && !empty($break['break_start']) && !empty($break['break_end'])) {
                        $breakStart = Carbon::createFromFormat('H:i', $break['break_start']);
                        $breakEnd = Carbon::createFromFormat('H:i', $break['break_end']);
                        if ($breakStart->gte($breakEnd)) {
                            $validator->errors()->add("breaks.{$key}.break_start", '休憩時間が不適切な値です');
                        }
                    }
                }
            }
        });
    }
}
