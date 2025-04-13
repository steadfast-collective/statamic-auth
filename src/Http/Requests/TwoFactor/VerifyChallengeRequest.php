<?php

namespace SteadfastCollective\StatamicAuth\Http\Requests\TwoFactor;

use Illuminate\Foundation\Http\FormRequest;

class VerifyChallengeRequest extends FormRequest
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
            'code' => [
                'sometimes',
                'required',
                'string'
            ],
            'recovery_code' => [
                'sometimes',
                'required',
                'string'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'code:required_without' => "The :attribute field is required",
            'recovery_code:required_without' => "The :attribute field is required",
        ];
    }
}