<?php

namespace App\Admin\Services;

use App\Central\Models\SystemSetting;
use Illuminate\Support\Arr;

class AdminSettingsService
{
    /**
     * Default values returned when a key has not yet been saved.
     *
     * @var array<string, mixed>
     */
    private array $defaults = [
        'app_name' => '',
        'app_url' => '',
        'support_email' => '',
        'allow_registration' => true,
        'require_email_verify' => true,
        'session_lifetime' => 120,
        'mail_driver' => 'smtp',
        'mail_from_address' => '',
        'mail_from_name' => '',
        'notify_new_signup' => true,
        'notify_payment_fail' => true,
        'notify_admin_email' => '',
        'max_storage_per_tenant_mb' => 1024,
        'allowed_file_types' => '',
    ];

    /**
     * Return all settings as a typed associative array, merging persisted
     * values on top of defaults.
     *
     * @return array<string, mixed>
     */
    public function getAll(): array
    {
        $persisted = SystemSetting::on('central')
            ->whereIn('key', array_keys($this->defaults))
            ->pluck('value', 'key')
            ->toArray();

        $merged = array_merge($this->defaults, $persisted);

        return $this->castValues($merged);
    }

    /**
     * Persist the given settings values as key/value rows.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(array $data): void
    {
        $allowed = Arr::only($data, array_keys($this->defaults));

        foreach ($allowed as $key => $value) {
            SystemSetting::on('central')->updateOrCreate(
                ['key' => $key],
                ['value' => $this->serializeValue($value)],
            );
        }
    }

    /**
     * Cast raw string values from the database back to their native types.
     *
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function castValues(array $settings): array
    {
        $booleanKeys = [
            'allow_registration',
            'require_email_verify',
            'notify_new_signup',
            'notify_payment_fail',
        ];

        $integerKeys = ['session_lifetime', 'max_storage_per_tenant_mb'];

        foreach ($booleanKeys as $key) {
            if (isset($settings[$key])) {
                $settings[$key] = filter_var($settings[$key], FILTER_VALIDATE_BOOLEAN);
            }
        }

        foreach ($integerKeys as $key) {
            if (isset($settings[$key])) {
                $settings[$key] = (int) $settings[$key];
            }
        }

        return $settings;
    }

    /**
     * Serialize a value to a string for storage.
     */
    private function serializeValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }
}
