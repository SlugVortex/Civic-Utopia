<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AiAgentUserSeeder extends Seeder
{
    public function run(): void
    {
        // We define the "Council" members here
        $agents = [
            [
                'name' => 'FactChecker',
                'email' => 'agent_factchecker@civicutopia.ai',
                'password' => 'factchecker_secret',
                'avatar' => 'https://ui-avatars.com/api/?name=Fact+Checker&background=198754&color=fff&size=128',
            ],
            [
                'name' => 'Historian',
                'email' => 'agent_historian@civicutopia.ai',
                'password' => 'historian_secret',
                'avatar' => 'https://ui-avatars.com/api/?name=The+Historian&background=8B4513&color=fff&size=128',
            ],
            [
                'name' => 'DevilsAdvocate',
                'email' => 'agent_advocate@civicutopia.ai',
                'password' => 'advocate_secret',
                'avatar' => 'https://ui-avatars.com/api/?name=Devils+Advocate&background=DC143C&color=fff&size=128',
            ],
            [
                'name' => 'Analyst',
                'email' => 'agent_analyst@civicutopia.ai',
                'password' => 'analyst_secret',
                'avatar' => 'https://ui-avatars.com/api/?name=Data+Analyst&background=4169E1&color=fff&size=128',
            ]
        ];

        foreach ($agents as $agent) {
            $user = User::firstOrCreate(
                ['email' => $agent['email']],
                [
                    'name' => $agent['name'],
                    'password' => Hash::make($agent['password']),
                ]
            );

            // If you have a profile_photo_path column, you can update it manually here
            // $user->profile_photo_path = $agent['avatar'];
            // $user->save();

            $this->command->info("AI Agent '{$agent['name']}' is ready.");
        }
    }
}
