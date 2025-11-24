<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'user_id', 'summary'];

    // Eager load media along with the user and comments
    protected $with = ['user', 'comments', 'media'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    // New relationship to the Media model
    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }
}
