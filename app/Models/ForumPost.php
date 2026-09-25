<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ForumPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'status',
        'post_image',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(ForumComment::class, 'post_id');
    }

    public function likes()
    {
        return $this->hasMany(ForumLike::class, 'post_id');
    }

    // Accessors
    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    public function getCommentsCountAttribute()
    {
        return $this->comments()->count();
    }

    public function getPostImageUrlAttribute()
    {
        if ($this->post_image) {
            $publicDisk = Storage::disk('public');
            $filename = basename($this->post_image);

            if ($publicDisk->exists($this->post_image)) {
                return '/storage/' . ltrim($this->post_image, '/');
            }

            foreach (['uploads/forum/', 'forum/'] as $directory) {
                $path = $directory . $filename;

                if ($publicDisk->exists($path)) {
                    return '/storage/' . ltrim($path, '/');
                }
            }

            foreach (['uploads/forum/', 'forum/'] as $directory) {
                $legacyPublicPath = public_path($directory . $filename);

                if (file_exists($legacyPublicPath)) {
                    return '/' . trim(str_replace('\\', '/', $directory . $filename), '/');
                }
            }
        }

        return null;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDeleted($query)
    {
        return $query->where('status', 'deleted');
    }

    // Methods
    public function isLikedBy($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    public function softDelete()
    {
        $this->status = 'deleted';
        $this->save();
    }

    public function getUserTypeAttribute(): ?string
    {
        if ($this->user) {
            return $this->user->role;
        }
        return null;
    }

    public function getUserRoleAttribute(): ?string
    {
        return $this->user_type;
    }
}
