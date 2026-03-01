<?php

namespace App\Http\Requests\Admin;

use App\Central\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
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
        /** @var Plan|null $plan */
        $plan = $this->route('plan');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'alpha_dash', Rule::unique('central.plans', 'slug')->ignore($plan?->id)],
            'description' => ['sometimes', 'nullable', 'string'],
            'price_monthly' => ['sometimes', 'required', 'integer', 'min:0'],
            'price_yearly' => ['sometimes', 'required', 'integer', 'min:0'],
            'trial_days' => ['sometimes', 'required', 'integer', 'min:0', 'max:365'],
            'features' => ['sometimes', 'nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'stripe_monthly_price_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'stripe_yearly_price_id' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
