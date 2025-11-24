<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BallotQuestion;

class BallotQuestionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Jamaica (Constitutional Reform)
        BallotQuestion::create([
            'country' => 'Jamaica',
            'region' => 'National',
            'title' => 'Referendum on the Republic Transition',
            'election_date' => now()->addMonths(2),
            'official_text' => 'A Bill Entitled: An Act to Amend the Constitution of Jamaica to provide for the replacement of the Governor-General with a Non-Executive President as the Head of State, to establish the Republic of Jamaica, and to sever formal ties with the British Monarchy.',
            'summary_plain' => 'Should Jamaica stop having the British Monarch as Head of State and elect a Jamaican President instead?',
            'summary_patois' => 'Yuh want di Queen/King gone? Dis vote is fi decide if we want cut ties wid di British Monarchy and have we own Jamaican President instead.',
            'yes_vote_meaning' => 'Jamaica becomes a Republic. The Governor-General is replaced by a President.',
            'no_vote_meaning' => 'Jamaica remains a Constitutional Monarchy with the British Sovereign as Head of State.',
            'pros' => ['Full independence.', 'National pride.', 'No foreign head of state.'],
            'cons' => ['Costs of changing symbols.', 'Current system is stable.', 'Risk of political bias in President selection.']
        ]);

        // 2. USA (California Prop 1)
        BallotQuestion::create([
            'country' => 'USA',
            'region' => 'California',
            'title' => 'Proposition 1: Mental Health Services Act',
            'election_date' => now()->addMonths(1),
            'official_text' => 'Bonds for Mental Health Treatment Facilities. Authorizes $6.38 billion in bonds to build mental health treatment facilities for those with mental health and substance use challenges; provides housing for the homeless.',
            'summary_plain' => 'Should the state borrow $6.4 billion to build housing and treatment centers for homeless people with mental health issues?',
            'summary_patois' => 'Dem want borrow nuff money ($6.4 billion) fi build house and hospital fi people weh mad or homeless. Yuh tink dat a good move?',
            'yes_vote_meaning' => 'The state will sell bonds to fund housing and treatment facilities.',
            'no_vote_meaning' => 'Funding for these facilities will remain as is without new debt.',
            'pros' => ['Addresses homelessness crisis.', 'Provides needed mental health beds.'],
            'cons' => ['Increases state debt.', 'Takes money from existing county programs.']
        ]);

        // 3. UK (Brexit Style / Theoretical)
        BallotQuestion::create([
            'country' => 'United Kingdom',
            'region' => 'Scotland',
            'title' => 'Scottish Independence Referendum',
            'election_date' => now()->addMonths(5),
            'official_text' => 'Should Scotland be an independent country? The Act provides for a transition period where assets and liabilities of the UK are divided.',
            'summary_plain' => 'Should Scotland leave the United Kingdom and become its own separate country?',
            'summary_patois' => 'Scotland people asking if dem fi split from England and rule dem own self completely. Left di UK business alone.',
            'yes_vote_meaning' => 'Scotland leaves the UK.',
            'no_vote_meaning' => 'Scotland stays in the UK.',
            'pros' => ['Control over own oil and economy.', 'Better representation in EU.'],
            'cons' => ['Economic uncertainty.', 'Hard border with England.']
        ]);
    }
}
