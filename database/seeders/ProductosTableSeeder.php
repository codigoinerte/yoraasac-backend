<?php

namespace Database\Seeders;

use App\Models\productos;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        productos::factory()->count(50)->create();
    }
}
