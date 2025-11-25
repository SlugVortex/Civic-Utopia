<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentAnnotation extends Model
{
    use HasFactory;

    protected $fillable = ['document_id', 'user_id', 'note', 'section_reference'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
