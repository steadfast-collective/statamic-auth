<?php

namespace SteadfastCollective\StatamicAuth\Http\Requests\Account;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
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
            'current_password' => [
                'required',
                function($attribute, $value, $fail) {
                    if (!Hash::check($value, Auth::user()->password)) {
                        $fail(__('statamic-auth::strings.account.password.incorrect'));
                    }
                }
            ],
            'new_password' => [
                'required',
                Password::defaults(),
                'confirmed',
                function($attribute, $value, $fail) {
                    if(Hash::check($value, Auth::user()->password)) {
                        $fail(__('statamic-auth::strings.account.password.must-not-match'));
                    }
                }
            ],
            'new_password_confirmation' => [
                'required'
            ]
        ];
    }
}