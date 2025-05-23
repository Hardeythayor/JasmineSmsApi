<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
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
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = request()->user()->id; //Route::input('user_id'); // Get the user ID from the route parameter

        return [
            'oldPassword' => [
                'required',
                function ($attribute, $value, $fail) use ($userId) {
                    $user = User::find($userId); // Use the User model
                    if (!$user) {
                        return $fail('Invalid User');
                    }

                    if (!Hash::check($value, $user->password)) {
                        return $fail('Incorrect Old Password');
                    }
                },
            ],
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
            'oldPassword.required' => 'The old password is required.', // Optional: Custom message for required
        ];
    }
}
