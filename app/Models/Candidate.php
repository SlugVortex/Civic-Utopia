<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'party',
        'office',
        'country', // <-- Added
        'region',  // <-- Added
        'photo_url',
        'manifesto_text',
        'stances',
        'ai_summary',
    ];

    protected $casts = [
        'stances' => 'array',
    ];
}
