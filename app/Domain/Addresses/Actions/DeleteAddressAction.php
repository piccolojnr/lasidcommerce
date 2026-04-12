<?php

namespace App\Domain\Addresses\Actions;

use App\Models\Address;

class DeleteAddressAction
{
    public function execute(Address $address): void
    {
        $address->delete();
    }
}
