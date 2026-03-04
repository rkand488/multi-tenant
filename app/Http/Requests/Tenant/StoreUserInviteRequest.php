<?php

namespace App\Http\Requests\Tenant;

use App\Central\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserInviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTenantOwner() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::in([UserRole::TenantUser->value, UserRole::TenantOwner->value])],
            'role_id' => ['nullable', 'integer', 'exists:central.roles,id'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'role.in' => 'Role must be one of: tenant_user, tenant_owner.',
            'email.required' => 'Please enter the email address to invite.',
        ];
    }
}
