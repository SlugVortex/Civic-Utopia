<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'type',
        'country',
        'file_path',
        'extracted_text',
        'summary_plain',
        'summary_eli5',
        'is_public',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function annotations()
    {
        return $this->hasMany(DocumentAnnotation::class)->latest();
    }
}
