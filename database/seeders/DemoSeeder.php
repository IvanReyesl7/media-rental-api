<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Media::create([
            'name' => 'Valla Autopista Norte', 'type' => 'billboard', 'location' => 'San Salvador',
            'dimensions' => '12x4 m', 'price_per_day' => 50, 'status' => 'active',
        ]);
        \App\Models\Media::create([
            'name' => 'Pantalla Centro', 'type' => 'screen', 'location' => 'Santa Tecla',
            'dimensions' => '6x3 m', 'price_per_day' => 80, 'status' => 'active',
        ]);
        \App\Models\Customer::create([
            'name' => 'Cliente Demo', 'email' => 'demo@example.com', 'phone' => '7000-0000',
        ]);
    }
}
