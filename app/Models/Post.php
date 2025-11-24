<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'user_id', 'summary'];

    protected $with = ['user', 'comments', 'media', 'topics'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class);
    }

    public function likers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_like');
    }

    public function bookmarkers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_bookmark');
    }

    /**
     * Check if the post is liked by the current user.
     * Appends an `is_liked` attribute to the model.
     */
    public function getIsLikedAttribute(): bool
    {
        if (!Auth()->check()) {
            return false;
        }
        return $this->likers()->where('user_id', Auth()->id())->exists();
    }

    /**
     * Check if the post is bookmarked by the current user.
     * Appends an `is_bookmarked` attribute to the model.
     */
    public function getIsBookmarkedAttribute(): bool
    {
        if (!Auth()->check()) {
            return false;
        }
        return $this->bookmarkers()->where('user_id', Auth()->id())->exists();
    }
}
