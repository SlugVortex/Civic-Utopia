<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AiAgentUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if the user already exists to avoid duplicates
        if (!User::where('email', 'historian@civicutopia.bot')->exists()) {
            User::create([
                'name' => 'The Historian',
                'email' => 'historian@civicutopia.bot',
                'password' => Hash::make(str()->random(32)), // Secure random password
            ]);
            $this->command->info('AI Agent "The Historian" created successfully.');
        } else {
            $this->command->info('AI Agent "The Historian" already exists.');
        }
    }
}
