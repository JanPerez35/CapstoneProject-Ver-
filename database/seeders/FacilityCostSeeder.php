<?php

namespace Database\Seeders;

use App\Models\FacilityCost;
use Illuminate\Database\Seeder;

class FacilityCostSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'classroom_name' => 'Cancha CM',
                'classroom_space' => 12082.00,
            ],
            [
                'classroom_name' => 'Lateral 1',
                'classroom_space' => 4706.00,
            ],
            [
                'classroom_name' => 'Lateral 2',
                'classroom_space' => 4706.00,
            ],
            [
                'classroom_name' => 'CM 201',
                'classroom_space' => 568.00,
            ],
            [
                'classroom_name' => 'CM 202',
                'classroom_space' => 564.00,
            ],
            [
                'classroom_name' => 'CM 203',
                'classroom_space' => 564.00,
            ],
            [
                'classroom_name' => 'CM 204',
                'classroom_space' => 568.00,
            ],
            [
                'classroom_name' => 'CM 210',
                'classroom_space' => 820.00,
            ],
        ];

        foreach ($rows as $row) {
            FacilityCost::updateOrCreate(
                ['classroom_name' => $row['classroom_name']],
                [
                    'supply_cost' => 0.00,
                    'electricity_cost' => 0.00,
                    'water_cost' => 0.00,

                    'lending_certificate_1' => 0.21,
                    'lending_certificate_2' => 0.26,
                    'lending_certificate_3' => 0.31,

                    'classroom_space' => $row['classroom_space'],

                    'daily_cost_1' => 0.21,
                    'daily_cost_2' => 0.26,
                    'daily_cost_3' => 0.31,

                    'weekly_cost_1' => 0.86,
                    'weekly_cost_2' => 1.03,
                    'weekly_cost_3' => 0.00,

                    'monthly_cost_1' => 2.74,
                    'monthly_cost_2' => 3.29,
                    'monthly_cost_3' => 0.00,
                ]
            );
        }
    }
}