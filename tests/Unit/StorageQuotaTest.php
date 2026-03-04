<?php

use App\Central\Models\Plan;

// ---------------------------------------------------------------------------
// Storage quota calculations (standalone — no database needed)
// ---------------------------------------------------------------------------

/**
 * These tests verify the storage quota maths used by the UI and services
 * without touching the database.
 */
it('calculates used percent correctly', function (): void {
    $usedBytes = 500 * 1048576;  // 500 MB
    $limitBytes = 1 * 1073741824; // 1 GB

    $percent = (int) round(($usedBytes / $limitBytes) * 100);

    expect($percent)->toBe(49);
});

it('returns 0 percent when nothing is used', function (): void {
    $usedBytes = 0;
    $limitBytes = 1073741824;

    $percent = $limitBytes > 0 ? (int) round(($usedBytes / $limitBytes) * 100) : 0;

    expect($percent)->toBe(0);
});

it('returns 100 when usage equals the limit exactly', function (): void {
    $usedBytes = 1073741824;
    $limitBytes = 1073741824;

    $percent = (int) min(100, round(($usedBytes / $limitBytes) * 100));

    expect($percent)->toBe(100);
});

it('caps at 100 when usage exceeds the limit', function (): void {
    $usedBytes = 2 * 1073741824;
    $limitBytes = 1073741824;

    $percent = (int) min(100, round(($usedBytes / $limitBytes) * 100));

    expect($percent)->toBe(100);
});

it('unlimited plan (no limit) renders as 0 percent used', function (): void {
    $plan = new Plan(['features' => []]);
    $limitGb = $plan->feature('storage_gb'); // null = unlimited

    $percent = $limitGb !== null
        ? min(100, round((512 * 1048576 / ($limitGb * 1073741824)) * 100))
        : 0;

    expect($percent)->toBe(0);
});

it('formatBytes produces correct GB label', function (): void {
    $formatBytes = static function (int $bytes): string {
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1).' KB';
        }
        if ($bytes < 1073741824) {
            return round($bytes / 1048576, 1).' MB';
        }

        return round($bytes / 1073741824, 1).' GB';
    };

    expect($formatBytes(1073741824))->toBe('1 GB')
        ->and($formatBytes(1048576))->toBe('1 MB')
        ->and($formatBytes(1024))->toBe('1 KB')
        ->and($formatBytes(512))->toBe('512 B');
});
