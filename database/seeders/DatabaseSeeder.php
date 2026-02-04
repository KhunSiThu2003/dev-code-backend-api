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
        // Verified admin (for testing)
        User::factory()
            ->verified()
            ->admin()
            ->create([
                'name'     => 'Admin User',
                'email'    => 'devcode.mm@gmail.com',
                'password' => 'asdffdsa',
            ]);

        // Verified instructor
        User::factory()
            ->verified()
            ->instructor()
            ->create([
                'name'  => 'Instructor User',
                'email' => 'instructor@gmail.com',
                'password' => 'asdffdsa'
            ]);

        // Fake verified learners (can log in immediately)
        User::factory(10)->verified()->create();

        // Optional: one known test learner
        User::factory()
            ->verified()
            ->create([
                'name'  => 'Test User',
                'email' => 'test@example.com',
            ]);
    }
}
