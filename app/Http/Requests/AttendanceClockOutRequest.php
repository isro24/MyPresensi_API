<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceClockOutRequest extends FormRequest
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
            'latitude_clock_out' => 'required|numeric',
            'longitude_clock_out' => 'numeric',
            'photo_clock_out' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'latitude_clock_out.required' => 'Latitude saat clock out wajib diisi.',
            'latitude_clock_out.numeric' => 'Latitude saat clock out harus berupa angka.',
            'longitude_clock_out.numeric' => 'Longitude saat clock out harus berupa angka.',
            'photo_clock_out.image' => 'File yang diunggah harus berupa gambar.',
            'photo_clock_out.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg, gif, svg.',
            'photo_clock_out.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}
