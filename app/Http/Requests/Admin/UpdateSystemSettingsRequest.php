<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:255'],
            'app_url' => ['nullable', 'url', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'allow_registration' => ['boolean'],
            'require_email_verify' => ['boolean'],
            'session_lifetime' => ['required', 'integer', 'min:1', 'max:43200'],
            'mail_driver' => ['required', 'string', 'in:smtp,ses,mailgun,postmark,log'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            'notify_new_signup' => ['boolean'],
            'notify_payment_fail' => ['boolean'],
            'notify_admin_email' => ['nullable', 'email', 'max:255'],
            'max_storage_per_tenant_mb' => ['required', 'integer', 'min:1'],
            'allowed_file_types' => ['nullable', 'string', 'max:255'],
        ];
    }
}
