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

    public function getUserAttribute()
    {
        return $this->user;
    }

    public function getUserIdAttribute(): ?int
    {
        return $this->user_id;
    }

    public function getUserTypeAttribute(): ?string
    {
        if ($this->user) {
            return $this->user->role;
        }
        return null;
    }
}
