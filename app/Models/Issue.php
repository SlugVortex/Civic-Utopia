<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'location',
        'image_path',
        'user_description',
        'ai_tags',
        'ai_caption',
        'severity',
        'generated_letter',
        'status',
    ];

    protected $casts = [
        'ai_tags' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
