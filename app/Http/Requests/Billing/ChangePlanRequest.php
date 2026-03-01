<?php

namespace App\Http\Requests\Billing;

use App\Central\Enums\BillingInterval;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'uuid', 'exists:central.plans,id'],
            'billing_interval' => ['required', Rule::enum(BillingInterval::class)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'plan_id.exists' => 'The selected plan does not exist.',
            'billing_interval.required' => 'A billing interval (monthly or yearly) is required.',
        ];
    }
}
