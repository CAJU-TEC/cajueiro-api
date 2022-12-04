<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    public function definition()
    {
        return [
            'description' => fake()->name(),
            'email' => fake()->email(),
            'logo' => null,
            'address' => fake()->address(),
        ];
    }
}
