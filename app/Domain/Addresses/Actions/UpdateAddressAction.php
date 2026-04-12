<?php

namespace App\Domain\Addresses\Actions;

use App\Models\Address;

class UpdateAddressAction
{
    public function execute(Address $address, array $data): Address
    {
        if (! empty($data['is_default'])) {
            Address::where('user_id', $address->user_id)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($data);

        return $address->fresh();
    }
}
