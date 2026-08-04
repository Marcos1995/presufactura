<?php

namespace Database\Factories;

use App\Models\SifEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SifEvent>
 */
class SifEventFactory extends Factory
{
    protected $model = SifEvent::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'event_type' => SifEvent::TYPE_STARTUP,
            'payload' => ['software' => config('verifactu.software.name')],
        ];
    }
}
