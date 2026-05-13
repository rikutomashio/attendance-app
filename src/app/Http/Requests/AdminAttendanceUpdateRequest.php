<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AdminAttendanceUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],

            'breaks' => ['nullable', 'array'],

            'breaks.*.start_time' => ['nullable', 'date_format:H:i'],
            'breaks.*.end_time' => ['nullable', 'date_format:H:i'],

            'reason' => ['required', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            if (!$this->clock_in || !$this->clock_out) {
                return;
            }

            $clockIn = Carbon::createFromFormat('H:i', $this->clock_in);
            $clockOut = Carbon::createFromFormat('H:i', $this->clock_out);

            // -------------------------
            // 勤怠チェック
            // -------------------------
            if ($clockIn->gt($clockOut)) {
                $validator->errors()->add(
                    'clock_in',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            // -------------------------
            // 休憩チェック
            // -------------------------
            $breaks = is_array($this->breaks) ? $this->breaks : [];

            foreach ($breaks as $index => $break) {

                $start = isset($break['start_time'])
                    ? Carbon::createFromFormat('H:i', $break['start_time'])
                    : null;

                $end = isset($break['end_time'])
                    ? Carbon::createFromFormat('H:i', $break['end_time'])
                    : null;

                /**
                 * ① start > end
                 */
                if ($start && $end && $start->gt($end)) {
                    $validator->errors()->add(
                        "breaks.$index.start_time",
                        '休憩時間が不適切な値です'
                    );
                    continue;
                }

                /**
                 * ② 勤務時間外チェック（開始）
                 */
                if ($start && ($start->lt($clockIn) || $start->gt($clockOut))) {
                    $validator->errors()->add(
                        "breaks.$index.start_time",
                        '休憩時間が不適切な値です'
                    );
                }

                /**
                 * ③ 勤務時間外チェック（終了）
                 */
                if ($end && ($end->lt($clockIn) || $end->gt($clockOut))) {
                    $validator->errors()->add(
                        "breaks.$index.end_time",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }
            }
        });
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_out.required' => '退勤時間を入力してください',

            'breaks.*.start_time.date_format' => '休憩開始時間の形式が不正です',
            'breaks.*.end_time.date_format' => '休憩終了時間の形式が不正です',

            'reason.required' => '備考を記入してください',
        ];
    }
}
