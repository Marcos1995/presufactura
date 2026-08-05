<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetUserPlanCommand extends Command
{
    protected $signature = 'presufactura:set-plan {email : Email del usuario} {plan : free o pro}';

    protected $description = 'Asigna plan free o pro a un usuario (útil para pruebas locales)';

    public function handle(): int
    {
        $email = $this->argument('email');
        $plan = strtolower($this->argument('plan'));

        if (! in_array($plan, ['free', 'pro'], true)) {
            $this->error('El plan debe ser "free" o "pro".');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No existe ningún usuario con email {$email}.");

            return self::FAILURE;
        }

        $updates = ['plan' => $plan];

        if ($plan === 'free') {
            $updates['stripe_subscription_id'] = null;
            $updates['plan_expires_at'] = null;
        } else {
            $updates['plan_expires_at'] = null;
        }

        $user->update($updates);

        $this->info("Usuario {$email} actualizado a plan {$plan}.");

        return self::SUCCESS;
    }
}
