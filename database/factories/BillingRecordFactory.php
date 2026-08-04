<?php

namespace Database\Factories;

use App\Models\BillingRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillingRecord>
 */
class BillingRecordFactory extends Factory
{
    protected $model = BillingRecord::class;

    public function definition(): array
    {
        return [
            'record_type' => BillingRecord::TYPE_ALTA,
            'xml_path' => null,
            'hash_current' => hash('sha256', fake()->uuid()),
            'hash_previous' => null,
            'aeat_status' => BillingRecord::STATUS_PENDING,
            'aeat_response' => null,
            'sent_at' => null,
        ];
    }
}
