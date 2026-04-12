<?php

namespace App\Domain\Addresses\Actions;

use App\Models\Address;
use App\Models\User;

class CreateAddressAction
{
    public function execute(User $user, array $data): Address
    {
        if (! empty($data['is_default'])) {
            Address::where('user_id', $user->id)->update(['is_default' => false]);
        }

        return Address::create(array_merge($data, ['user_id' => $user->id]));
    }
}
