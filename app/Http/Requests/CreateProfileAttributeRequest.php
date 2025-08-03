<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProfileAttributeRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'key_name' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'data_type' => ['in:string,text,email,url,number,boolean'],
            'value' => ['required', 'string', 'max:1000'],
            'visibility' => ['in:private,protected,public'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'key_name.required' => 'Please provide a key name for the attribute.',
            'key_name.regex' => 'Key name must contain only lowercase letters, numbers, and underscores.',
            'value.required' => 'Please provide a value for the attribute.',
            'visibility.in' => 'Visibility must be private, protected, or public.',
        ];
    }
}
