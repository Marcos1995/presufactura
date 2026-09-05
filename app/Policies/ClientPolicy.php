<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function view(User $user, Client $client): bool
    {
        return $this->owns($user, $client);
    }

    public function update(User $user, Client $client): bool
    {
        return $this->owns($user, $client);
    }

    public function delete(User $user, Client $client): bool
    {
        return $this->owns($user, $client);
    }

    private function owns(User $user, Client $client): bool
    {
        return $user->id === $client->user_id
            && (! $client->company_id || $client->company?->user_id === $user->id);
    }
}
