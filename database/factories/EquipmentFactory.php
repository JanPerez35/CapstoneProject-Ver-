<?php

namespace Database\Factories;

use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => 'Test equipment',
            'category' => 'Test',
            'location' => 'Test location',
            'quantity' => 10,
            'available_quantity' => 10,
            'permissions' => true,
            'pending_deletion' => false,
        ];
    }
}
