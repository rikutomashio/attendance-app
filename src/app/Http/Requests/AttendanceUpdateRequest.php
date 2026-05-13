<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    // ★ 基本バリデーション
    public function rules()
    {
        return [
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time'],

            'breaks.*.start_time' => ['nullable'],
            'breaks.*.end_time' => ['nullable'],

            'note' => ['required'],
        ];
    }

    // ★ エラーメッセージ
    public function messages()
    {
        return [
            'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'note.required' => '備考を記入してください',
        ];
    }

    // ★ 追加チェック（休憩）
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            foreach ($this->breaks ?? [] as $index => $break) {

                if (empty($break['start_time']) || empty($break['end_time'])) {
                    continue;
                }

                // 休憩開始が勤務外
                if (
                    $break['start_time'] < $this->start_time ||
                    $break['start_time'] > $this->end_time
                ) {
                    $validator->errors()->add(
                        "breaks.$index.start_time",
                        '休憩時間が不適切な値です'
                    );
                }

                // ★ 休憩開始 > 休憩終了
                if ($break['start_time'] > $break['end_time']) {
                    $validator->errors()->add(
                        "breaks.$index.start_time",
                        '休憩時間が不適切な値です'
                    );
                }

                // 休憩終了が退勤より後
                if ($break['end_time'] > $this->end_time) {
                    $validator->errors()->add(
                        "breaks.$index.end_time",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }
            }
        });
    }
}
