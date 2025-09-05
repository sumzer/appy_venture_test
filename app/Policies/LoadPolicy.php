<?php
namespace App\Policies;

use App\Models\Load;
use App\Models\User;

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
}
