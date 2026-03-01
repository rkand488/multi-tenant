<?php

namespace App\Http\Requests\Auth;

use App\Central\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorised via InvitationPolicy
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'role' => ['required', Rule::in([UserRole::TenantUser->value, UserRole::TenantOwner->value])],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'role.in' => 'Role must be one of: tenant_user, tenant_owner.',
        ];
    }
}
