<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceClockInRequest extends FormRequest
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
            'latitude_clock_in' => 'required|numeric',
            'longitude_clock_in' => 'required|numeric',
            'photo_clock_in' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'note' => 'nullable|string|max:255',
        ];
    }

    public function show(): array
    {
        return [
            'latitude_clock_in.required' => 'Latitude saat clock in wajib diisi.',
            'latitude_clock_in.numeric' => 'Latitude saat clock in harus berupa angka.',
            'longitude_clock_in.required' => 'Longitude saat clock in wajib diisi.',
            'photo_clock_in.image' => 'File yang diunggah harus berupa gambar.',
            'photo_clock_in.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg, gif, svg.',
            'photo_clock_in.max' => 'Ukuran gambar maksimal 2MB.',
            'note.string' => 'Catatan harus berupa teks.',
            'note.max' => 'Catatan tidak boleh lebih dari 255 karakter.',
        ];
    }
}
