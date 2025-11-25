<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BallotQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'official_text',
        'election_date',
        'country',       // <-- Added
        'region',        // <-- Added
        'summary_plain',
        'summary_patois',
        'yes_vote_meaning',
        'no_vote_meaning',
        'pros',
        'cons',
    ];

    protected $casts = [
        'pros' => 'array',
        'cons' => 'array',
        'election_date' => 'date',
    ];
}
