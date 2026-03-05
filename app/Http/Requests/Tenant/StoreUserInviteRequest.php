<?php

namespace App\Http\Requests\Tenant;

use App\Central\Enums\UserRole;
use App\Tenancy\TenantQueryExecutor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
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
            'role_id' => [
                'nullable',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null) {
                        return;
                    }

                    $roleId = (int) $value;

                    $existsInTenantDatabase = app(TenantQueryExecutor::class)
                        ->runTenant(
                            fn ($tenantConnection) => $tenantConnection
                                ->table('roles')
                                ->where('id', $roleId)
                                ->exists()
                        );

                    if ($existsInTenantDatabase === true) {
                        return;
                    }

                    $user = $this->user();

                    $existsInCentral = DB::connection('central')
                        ->table('roles')
                        ->where('id', $roleId)
                        ->where('tenant_id', $user?->tenant_id)
                        ->exists();

                    if (! $existsInCentral) {
                        $fail('The selected role is invalid.');
                    }
                },
            ],
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
