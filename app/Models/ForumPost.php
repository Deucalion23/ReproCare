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
        'post_image_data',
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
        $inline = $this->getAttribute('post_image_data');
        if (is_string($inline) && str_starts_with($inline, 'data:image/')) {
            return $inline;
        }

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

    /**
     * Make a database-safe copy of an attachment so it survives ephemeral
     * application disks (such as Render's default filesystem).
     */
    public static function makePostImageDataUrl($file): ?string
    {
        try {
            if (! $file || ! method_exists($file, 'getRealPath') || ! is_file($file->getRealPath())) {
                return null;
            }

            $raw = @file_get_contents($file->getRealPath());
            if ($raw === false) {
                return null;
            }

            // Resize uploads before encoding, keeping a detailed but practical
            // attachment size for the database and page load.
            if (function_exists('imagecreatefromstring') && function_exists('imagecreatetruecolor')
                && function_exists('imagejpeg') && function_exists('imagesx') && function_exists('imagesy')) {
                $source = @imagecreatefromstring($raw);
                if ($source !== false) {
                    $width = imagesx($source);
                    $height = imagesy($source);
                    if ($width > 0 && $height > 0) {
                        $max = 1200;
                        $scale = min(1, $max / max($width, $height));
                        $newWidth = max(1, (int) round($width * $scale));
                        $newHeight = max(1, (int) round($height * $scale));
                        $resized = imagecreatetruecolor($newWidth, $newHeight);
                        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                        ob_start();
                        imagejpeg($resized, null, 80);
                        $jpeg = ob_get_clean();
                        imagedestroy($source);
                        imagedestroy($resized);

                        if ($jpeg !== false && strlen($jpeg) <= 800000) {
                            return 'data:image/jpeg;base64,' . base64_encode($jpeg);
                        }
                    } else {
                        imagedestroy($source);
                    }
                }
            }

            // If GD is unavailable, preserve smaller valid image uploads.
            $mime = method_exists($file, 'getMimeType') ? $file->getMimeType() : null;
            if (is_string($mime) && str_starts_with($mime, 'image/') && strlen($raw) <= 500000) {
                return 'data:' . $mime . ';base64,' . base64_encode($raw);
            }
        } catch (\Throwable $e) {
            // The normal file upload remains usable if an inline copy cannot be made.
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
