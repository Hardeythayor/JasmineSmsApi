<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendSmsRequest extends FormRequest
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
            'sendMode' => [ // Add this rule
                'required',
                Rule::in(['immediately', 'reserved']),
            ],
            'sendDate' => [ // Conditional rule for sendDate
                Rule::requiredIf(function () {
                    return $this->input('sendMode') === 'reserved';
                }),
                'date'
            ],
            'splitSend' => [ // Add this rule
                'required',
                Rule::in(['yes', 'no']),
            ],
            'splitNumber' => [ // Conditional rule for sendDate
                Rule::requiredIf(function () {
                    return $this->input('splitSend') === 'yes';
                }),
                // 'numeric'
            ],
            'recipients' => 'required|array',
            'recipientCount' => 'required|numeric|min:1',
            'content' => 'required'
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
            'sendMode.required' => 'The send mode field is required.', //Custom message
            'sendMode.in' => 'The send mode must be either "immediately" or "reserved".', //Custom message
            'sendDate.required' => 'The send date field is required when send mode is "reserved".',
            'sendDate.date' => 'The send date must be a valid date and time.', // Custom message for date validation
            'splitSend.required' => 'The split sending field is required.', //Custom message
            'splitSend.in' => 'The split sending must be either "yes" or "no".', //Custom message
            'splitNumber.required' => 'The split number field is required when split send is "yes".',
        ];
    }
}
