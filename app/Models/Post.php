<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'user_id',
        'summary',
        'is_flagged',
        'flag_reason',
        'is_private',
        'topic_id'
    ];

    protected $with = ['user', 'comments', 'media', 'topics'];

    // --- SCOPES ---

    /**
     * Filter posts to show:
     * 1. All Public Posts (is_private = false)
     * 2. Private Posts belonging ONLY to the current user
     */
    public function scopeVisibleTo($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('is_private', false)
              ->orWhere(function($subQuery) use ($userId) {
                  $subQuery->where('is_private', true)
                           ->where('user_id', $userId);
              });
        });
    }

    // --- RELATIONSHIPS ---

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

    // --- ATTRIBUTES ---

    public function getIsLikedAttribute(): bool
    {
        if (!Auth::check()) return false;
        return $this->likers()->where('user_id', Auth::id())->exists();
    }

    public function getIsBookmarkedAttribute(): bool
    {
        if (!Auth::check()) return false;
        return $this->bookmarkers()->where('user_id', Auth::id())->exists();
    }
}
