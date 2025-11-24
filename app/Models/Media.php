<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'post_id', 'disk', 'path', 'file_type', 'mime_type'];

    protected $appends = ['file_url'];

    public function getFileUrlAttribute()
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}
