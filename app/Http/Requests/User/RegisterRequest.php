<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
            'userId' => 'required|unique:users',
            'inviteCode' => [
                'required',
                Rule::exists('invitation_codes', 'invite_code'), // Use Rule::exists
            ],
            'email' => 'nullable',
            'name' => 'required',
            'password' => [
                'required',
                'confirmed',
                Password::defaults(),
            ]
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'inviteCode.exists' => 'The provided invitation code is invalid.',
        ];
    }
}
