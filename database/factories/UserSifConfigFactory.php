<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserSifConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserSifConfig>
 */
class UserSifConfigFactory extends Factory
{
    protected $model = UserSifConfig::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'mode' => UserSifConfig::MODE_VERIFACTU,
            'cert_path' => null,
            'cert_expires_at' => null,
            'enabled' => false,
        ];
    }

    public function enabled(): static
    {
        return $this->state(fn () => ['enabled' => true]);
    }
}
