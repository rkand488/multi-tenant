<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTenantOwner() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $tenantId = $this->user()?->tenant_id;

        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'required',
                'string',
                'max:63',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('central.tenants', 'slug')->ignore($tenantId),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'slug.regex' => 'The workspace URL may only contain lowercase letters, numbers, and hyphens.',
            'slug.unique' => 'This workspace URL is already taken.',
            'authorize' => 'Only the workspace owner can update team settings.',
        ];
    }
}
