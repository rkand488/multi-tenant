<?php

namespace App\Tenant\Services;

use App\Central\Models\Tenant;

class TenantTeamSettingsService
{
    /**
     * @return array<string, mixed>
     */
    public function getSettings(Tenant $tenant): array
    {
        return data_get($tenant->extra ?? [], 'team_settings', []);
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    public function updateSettings(Tenant $tenant, array $settings): Tenant
    {
        $extra = $tenant->extra ?? [];
        $extra['team_settings'] = $settings;

        $tenant->update(['extra' => $extra]);

        return $tenant->fresh();
    }
}
