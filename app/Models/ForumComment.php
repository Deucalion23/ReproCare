<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'content',
    ];

    // Relationships
    public function post()
    {
        return $this->belongsTo(ForumPost::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // NOTE: no getUserAttribute() here on purpose — $comment->user must
    // resolve through the user() belongsTo relation below. A same-named
    // accessor would shadow it and fatal with "Undefined property".

    public function getUserIdAttribute(): ?int
    {
        // Read the raw row value directly: $this->user_id would re-enter
        // this same accessor through Eloquent magic and fatal.
        $value = $this->attributes['user_id'] ?? null;

        return $value === null ? null : (int) $value;
    }

    public function getUserTypeAttribute(): ?string
    {
        if ($this->user) {
            return $this->user->role;
        }
        return null;
    }
}
