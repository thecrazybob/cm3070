<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContextRequest extends FormRequest
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
        $context = $this->route('context');

        return [
            'slug' => [
                'sometimes',
                'string',
                'max:50',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('contexts')->where(function ($query) use ($context) {
                    return $query->where('user_id', $this->user()->id)
                        ->where('id', '!=', $context->id);
                }),
            ],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_default' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'Slug must contain only lowercase letters, numbers, and hyphens.',
            'slug.unique' => 'You already have a context with this slug.',
        ];
    }
}
