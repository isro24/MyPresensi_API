<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
        ];
    }

    public function messages(): array
    {
        return [
            'start_time.required' => 'Waktu mulai wajib diisi.',
            'start_time.date_format' => 'Format waktu mulai harus H:i:s.',
            'end_time.required' => 'Waktu selesai wajib diisi.',
            'end_time.date_format' => 'Format waktu selesai harus H:i:s.',
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai.',
        ];
    }
}
