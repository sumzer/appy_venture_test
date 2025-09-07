<?php

namespace App\Policies;

use App\Models\Bid;
use App\Models\Load;
use App\Models\User;
use App\Enums\LoadStatus;
class BidPolicy
{
    public function index(User $user, Load $load): bool
    {
        return $user->id === $load->shipper_id;
    }

    public function store(User $user, Load $load): bool
    {
        return $user->isCarrier() && $load->status === LoadStatus::Open;
    }

    public function accept(User $user, Bid $bid): bool
    {
        $load = $bid->freight;
        if (!$load)
            return false;

        return $user->id === $load->shipper_id
            && $load->status === LoadStatus::Open
            && !$load->booking;
    }
}
