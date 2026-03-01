<?php

namespace Database\Factories;

use App\Central\Enums\BillingInterval;
use App\Central\Enums\InvoiceStatus;
use App\Central\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /** @var class-string<Invoice> */
    protected $model = Invoice::class;

    private static int $invoiceNumber = 0;

    public function definition(): array
    {
        $amountDue = fake()->randomElement([2900, 4900, 9900]);
        $start = now()->startOfMonth();

        return [
            'tenant_id' => Str::uuid()->toString(),
            'subscription_id' => null,
            'number' => 'INV-'.str_pad((string) (++self::$invoiceNumber), 4, '0', STR_PAD_LEFT),
            'status' => InvoiceStatus::Open,
            'amount_due' => $amountDue,
            'amount_paid' => 0,
            'currency' => 'usd',
            'billing_interval' => BillingInterval::Monthly,
            'period_start' => $start,
            'period_end' => $start->copy()->addMonth(),
            'due_date' => now()->addDays(7)->toDateString(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => InvoiceStatus::Paid,
            'amount_paid' => $attrs['amount_due'],
            'paid_at' => now(),
        ]);
    }
}
