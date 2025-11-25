<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Candidate;

class CandidateSeeder extends Seeder
{
    public function run(): void
    {
        // Candidate 1: JLP Style (Focus on Infrastructure/Economy)
        Candidate::create([
            'name' => 'Andrew Holness (Incumbent)',
            'party' => 'JLP',
            'office' => 'Prime Minister',
            'manifesto_text' => 'Our administration is focused on building a new Jamaica through infrastructure development. We believe in fiscal responsibility, reducing debt to GDP, and creating jobs through the digital economy. We are implementing SOEs (States of Emergency) to curb gang violence and investing heavily in the new highway network to connect the countryside to the city.',
            'ai_summary' => 'Focuses on economic stability, infrastructure construction (highways), and using emergency powers to control crime.',
            'stances' => [
                'Economy' => 'Focus on debt reduction and infrastructure spending.',
                'Crime' => 'Supports States of Emergency (SOEs) and increased police funding.',
                'Education' => 'Promoting STEM and coding in schools.'
            ]
        ]);

        // Candidate 2: PNP Style (Focus on Social Welfare/Rights)
        Candidate::create([
            'name' => 'Mark Golding (Opposition)',
            'party' => 'PNP',
            'office' => 'Prime Minister',
            'manifesto_text' => 'It is time for a Jamaica that works for the many, not the few. We prioritize social intervention over military policing. We believe in strengthening the safety net for the poor, providing free education up to the tertiary level, and fixing the healthcare crisis. We want to focus on community policing rather than States of Emergency.',
            'ai_summary' => 'Focuses on social welfare, free education, and community-based approaches to crime reduction.',
            'stances' => [
                'Economy' => 'Focus on wealth redistribution and social safety nets.',
                'Crime' => 'Opposes SOEs; supports community intervention and social programs.',
                'Education' => 'Advocates for free tertiary education for all Jamaicans.'
            ]
        ]);
    }
}
