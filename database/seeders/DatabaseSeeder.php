<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin SDM',
            'email' => 'admin@siosmar.id',
            'password' => bcrypt('password'),
        ]);

        $this->call([
            PegawaiSeeder::class,
        ]);
    }
}

// bikin akun PHP Artisan Tinker

// User::create([
//     'name'  => 'Sampel',
//     'email' => 'sampel@gmail.com',
//     'password'  => 'password',
// ]);
