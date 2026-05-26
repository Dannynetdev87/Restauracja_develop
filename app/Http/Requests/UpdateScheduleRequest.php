<?php

namespace App\Http\Requests;

use App\Models\Schedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $schedule = $this->route('schedule');

        return $schedule instanceof Schedule
            && ($this->user()?->can('update', $schedule) ?? false);
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('is_active', true),
            ],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->hasScheduleConflict()) {
                $validator->errors()->add('start_time', 'Pracownik ma już zmianę w tym przedziale godzin.');
            }
        });
    }

    private function hasScheduleConflict(): bool
    {
        $schedule = $this->route('schedule');

        return Schedule::query()
            ->where('user_id', $this->integer('user_id'))
            ->whereDate('date', $this->input('date'))
            ->where('start_time', '<', $this->input('end_time'))
            ->where('end_time', '>', $this->input('start_time'))
            ->when($schedule instanceof Schedule, fn ($query) => $query->whereKeyNot($schedule->id))
            ->exists();
    }
}
