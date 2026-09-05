<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'legal_name' => 'Empresa Test SL',
            'tax_id' => 'B12345678',
            'address' => 'Calle Falsa 1',
            'city' => 'Madrid',
            'postal_code' => '28001',
            'province' => 'Madrid',
            'country' => 'ES',
            'email' => fake()->unique()->companyEmail(),
            'iban' => 'ES9121000418450200051332',
            'default_vat_rate' => 21,
            'invoice_prefix' => 'FAC',
            'quote_prefix' => 'PRE',
            'is_default' => true,
            'is_active' => true,
        ];
    }
}
