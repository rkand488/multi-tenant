<?php

namespace App\Http\Requests\Web\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('central.plans', 'slug')],
            'description' => ['nullable', 'string'],
            'price_monthly' => ['required', 'numeric', 'min:0'], // Web accepts dollars
            'price_yearly' => ['required', 'numeric', 'min:0'], // Web accepts dollars
            'trial_days' => ['required', 'integer', 'min:0', 'max:365'],
            'features' => ['nullable', 'array'],
            'features.max_users' => ['nullable', 'integer'],
            'features.max_storage_mb' => ['nullable', 'integer'],
            'features.api_access' => ['nullable', 'boolean'],
            'features.sso' => ['nullable', 'boolean'],
            'features.custom_domain' => ['nullable', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
