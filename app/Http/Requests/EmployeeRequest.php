<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
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
        $userId = $this->route('id'); // pastikan sesuai dengan parameter route

        return [
            'name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => $this->isMethod('post') ? 'required|string|min:6' : 'nullable|string|min:6',
            'nip' => [
                'required',
                'string',
                Rule::unique('employee', 'nip')->ignore($userId, 'user_id'),
            ],
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'gender' => 'required|in:Laki-Laki,Perempuan',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }


    public function messages(): array
    {
        return [
            'email.unique' => 'Email sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'nip.unique' => 'NIP sudah digunakan.',
        ];
    }
}
