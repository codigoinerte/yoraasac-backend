<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        for ($i = 0; $i < 1000; $i++) {
            DB::table('users')->insert([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'email_verified_at' => now(),
                'password' => Hash::make('password2016'),
                'apellidos' => $faker->lastName,
                'documento' => $faker->randomNumber(8),
                'documento_tipo' => $faker->randomElement([1,2,3,4,5,6]),
                'usuario_tipo' => $faker->randomElement([1, 2, 3, 4, 5, 6]),
                'idpais' => 0,
                'iddepartamento' => 0,
                'idprovincia' => 0,
                'iddistrito' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
