<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/equipment.csv');

        if (!file_exists($path)) {
            $this->command->error("CSV file not found at: {$path}");
            return;
        }

        $file = fopen($path, 'r');

        if ($file === false) {
            $this->command->error("Could not open CSV file.");
            return;
        }

        $header = fgetcsv($file);

        if ($header === false) {
            $this->command->error("CSV file is empty.");
            fclose($file);
            return;
        }

        $rows = [];

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) !== count($header)) {
                continue;
            }

            $data = array_combine($header, $row);

            $rows[] = [
                'category' => $data['category'],
                'quantity' => (int) $data['quantity'],
                'description' => $data['description'] !== '' ? $data['description'] : null,
                'available_quantity' => (int) $data['available_quantity'],
                'location' => $data['location'],
                'equipment_photo_url' => $data['equipment_photo_url'] !== '' ? $data['equipment_photo_url'] : null,
                'stats' => $data['stats'] !== '' ? (float) $data['stats'] : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        fclose($file);

        DB::table('equipment')->delete();

        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('equipment')->insert($chunk);
        }

        $this->command->info('Equipment data imported successfully.');
    }
}