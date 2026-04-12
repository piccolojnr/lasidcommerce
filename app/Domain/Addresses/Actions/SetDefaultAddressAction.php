<?php

namespace App\Domain\Addresses\Actions;

use App\Models\Address;

class SetDefaultAddressAction
{
    public function execute(Address $address): Address
    {
        Address::where('user_id', $address->user_id)
            ->where('id', '!=', $address->id)
            ->update(['is_default' => false]);

        $address->update(['is_default' => true]);

        return $address->fresh();
    }
}
