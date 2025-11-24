<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BallotQuestion;

class BallotQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BallotQuestion::create([
            'title' => 'Referendum on the Transition to a Republic',
            'election_date' => now()->addMonths(2),
            'official_text' => 'A Bill Entitled: An Act to Amend the Constitution of Jamaica to provide for the replacement of the Governor-General with a Non-Executive President as the Head of State, to establish the Republic of Jamaica, and to sever formal ties with the British Monarchy, while maintaining membership within the Commonwealth of Nations.',

            // Pre-filling these to show what AI WOULD generate.
            // In the real app, we can have a button to regenerate these via Azure.
            'summary_plain' => 'This question asks if Jamaica should stop having the British King/Queen as the Head of State and instead have a Jamaican President. Jamaica would become a Republic but stay in the Commonwealth.',
            'summary_patois' => 'Listen nuh: This asking if yuh want done wid the Queen/King business. Instead a having the British Monarchy rule we head, we woulda have a Jamaican President. We still par wid the Commonwealth, but we a run we own show officially as a Republic.',
            'yes_vote_meaning' => 'Voting YES means you support removing the British Monarch as Head of State and appointing a Jamaican President.',
            'no_vote_meaning' => 'Voting NO means you want to keep the current system where the British Monarch is the Head of State, represented by the Governor-General.',
            'pros' => [
                'Complete political independence from Britain.',
                'A Jamaican Head of State represents the people better.',
                'Removes symbols of colonial past.'
            ],
            'cons' => [
                'Costs associated with changing the constitution and symbols.',
                'Concerns about how the new President will be selected.',
                'Current system provides stable governance.'
            ]
        ]);
    }
}
