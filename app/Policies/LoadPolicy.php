<?php
namespace App\Policies;

use App\Models\Load;
use App\Models\User;
use App\Enums\LoadStatus;

class LoadPolicy
{
    public function manage(User $user, Load $load): bool
    {
        return $user->id === $load->shipper_id;
    }
    public function accept(User $user, Load $load): bool
    {
        return $this->manage($user, $load);
    }

    public function update(User $user, Load $load): bool
    {
        return $this->manage($user, $load)
            && in_array($load->status->value, [
                LoadStatus::Draft->value,
                LoadStatus::Open->value,
            ], true);
    }

    public function delete(User $user, Load $load): bool
    {
        return $this->manage($user, $load) && in_array($load->status->value, [
            LoadStatus::Draft->value,
            LoadStatus::Open->value,
            LoadStatus::Closed->value,
        ], true);
    }
}
