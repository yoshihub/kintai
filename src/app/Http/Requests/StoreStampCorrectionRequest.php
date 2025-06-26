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
            'end_time' => 'required|date_format:H:i|after:start_time',
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
            'attendance_id.required' => '勤怠IDが必要です',
            'attendance_id.exists' => '指定された勤怠記録が見つかりません',
            'start_time.required' => '出勤時間を入力してください',
            'start_time.date_format' => '出勤時間の形式が正しくありません',
            'end_time.required' => '退勤時間を入力してください',
            'end_time.date_format' => '退勤時間の形式が正しくありません',
            'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'breaks.array' => '休憩時間の形式が正しくありません',
            'breaks.*.break_start.date_format' => '休憩開始時間の形式が正しくありません',
            'breaks.*.break_end.date_format' => '休憩終了時間の形式が正しくありません',
            'note.required' => '備考を記入してください',
            'note.string' => '備考は文字列で入力してください',
        ];
    }

    /**
     * バリデーション後の追加チェック
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('breaks') && $this->end_time) {
                $endTime = Carbon::createFromFormat('H:i', $this->end_time);

                foreach ($this->breaks ?? [] as $key => $break) {
                    // パターン2: 休憩開始時間を退勤時間より後に設定
                    if (!empty($break['break_start'])) {
                        $breakStart = Carbon::createFromFormat('H:i', $break['break_start']);
                        if ($breakStart->gt($endTime)) {
                            $validator->errors()->add("breaks.{$key}.break_start", '休憩時間が不適切な値です');
                        }
                    }

                    // パターン3: 休憩終了時間を退勤時間より後に設定
                    if (!empty($break['break_end'])) {
                        $breakEnd = Carbon::createFromFormat('H:i', $break['break_end']);
                        if ($breakEnd->gt($endTime)) {
                            $validator->errors()->add("breaks.{$key}.break_end", '出勤時間もしくは退勤時間が不適切な値です');
                        }
                    }
                }
            }
        });
    }
}
