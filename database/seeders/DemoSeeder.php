<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Media;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['billboard', 'screen', 'mupi', 'wall'];
        $cities = ['San Salvador', 'Santa Tecla', 'San Miguel', 'Santa Ana', 'Soyapango'];

        foreach (range(1, 14) as $i) {
            Media::create([
                'name' => 'Medio ' . $i,
                'type' => $types[$i % 4],
                'location' => $cities[$i % 5],
                'dimensions' => (($i % 3) + 1) * 3 . 'x' . (($i % 2) + 2) . ' m',
                'price_per_day' => 40 + $i * 5,
                'status' => $i % 6 === 0 ? 'inactive' : 'active',
            ]);
        }

        Customer::create([
            'name' => 'Cliente Demo',
            'email' => 'demo@example.com',
            'phone' => '7000-0000',
        ]);
    }
}