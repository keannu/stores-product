<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'store_name'             => fake()->company(),
            'description'            => fake()->sentence(),
            'address'                => fake()->address(),
            'owner_name'             => fake()->name(),
            'mobile_number'          => fake()->phoneNumber(),
            'email'                  => fake()->unique()->companyEmail(),
            'admin_redirect_link'    => '/',
            'customer_redirect_link' => '/',
        ];
    }
}
