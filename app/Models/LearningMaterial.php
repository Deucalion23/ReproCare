<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;

class LearningMaterial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'material_type',
        'link_url',
        'image',
        'file',
        'category',
        'video_url',
        'youtube_id',
        'quiz_data',
        'week_number',
    ];

    protected static function booted(): void
    {
        // Keep youtube_id in sync whenever a YouTube link is saved.
        static::saving(function (LearningMaterial $material) {
            if (empty($material->youtube_id) && !empty($material->video_url)) {
                $material->youtube_id = self::extractYoutubeId($material->video_url);
            }
        });
    }

    /**
     * Extract a clean 11-character YouTube video ID from watch, Shorts,
     * youtu.be share, embed, or live URLs. Returns null when invalid.
     */
    public static function extractYoutubeId(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $url = trim($url);

        $patterns = [
            '/youtube\.com\/watch\?(?:.*[?&])?v=([\w-]{11})/i',
            '/youtube\.com\/shorts\/([\w-]{11})/i',
            '/youtube\.com\/embed\/([\w-]{11})/i',
            '/youtube\.com\/live\/([\w-]{11})/i',
            '/youtu\.be\/([\w-]{11})/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $m)) {
                return $m[1];
            }
        }

        return null;
    }

    /**
     * Resolved YouTube ID — stored value first, else extracted from video_url.
     */
    public function getYoutubeIdAttribute(): ?string
    {
        $stored = $this->attributes['youtube_id'] ?? null;
        if ($stored && preg_match('/^[\w-]{11}$/', $stored)) {
            return $stored;
        }

        return self::extractYoutubeId($this->attributes['video_url'] ?? null);
    }

    public function isYoutubeVideo(): bool
    {
        return !empty($this->youtube_id);
    }

    /**
     * Privacy-enhanced nocookie embed URL for the YouTube iframe player.
     */
    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        if (!$this->youtube_id) {
            return null;
        }

        return 'https://www.youtube-nocookie.com/embed/' . $this->youtube_id . '?rel=0&modestbranding=1';
    }

    /**
     * High-quality YouTube thumbnail for card covers.
     */
    public function getYoutubeThumbnailUrlAttribute(): ?string
    {
        if (!$this->youtube_id) {
            return null;
        }

        return 'https://img.youtube.com/vi/' . $this->youtube_id . '/hqdefault.jpg';
    }

    /**
     * Topic category badge label (e.g. "Prenatal Care", "Trimester 1 Guide").
     */
    public function getTopicBadgeAttribute(): string
    {
        if (!empty($this->week_number)) {
            $week = (int) $this->week_number;
            $trimester = $week <= 13 ? 1 : ($week <= 27 ? 2 : 3);
            return "Trimester {$trimester} Guide";
        }

        return match ($this->category) {
            'prenatal-care'   => 'Prenatal Care',
            'nutrition'       => 'Maternal Nutrition',
            'warning-signs'   => 'Warning Signs',
            'family-planning' => 'Family Planning',
            'postpartum'      => 'Postpartum Care',
            'hcw-training'    => 'HCW Training',
            default           => ucfirst(str_replace('-', ' ', $this->category ?? 'General')),
        };
    }

    /**
     * Key teaching points parsed from the content body (one per line).
     */
    public function getTeachingPointsAttribute(): array
    {
        $lines = preg_split('/\r\n|\r|\n/', (string) ($this->content ?? ''));
        $points = [];
        foreach ($lines as $line) {
            $line = trim($line, " \t\n\r\0\x0B-•*");
            if ($line !== '') {
                $points[] = $line;
            }
            if (count($points) >= 6) {
                break;
            }
        }
        return $points;
    }

    protected $casts = [
        'quiz_data' => 'array',
    ];

    // Scopes
    public function scopeArticles($query)
    {
        return $query->where('material_type', 'article');
    }

    public function scopeLinks($query)
    {
        return $query->where('material_type', 'link');
    }

    public function scopeFiles($query)
    {
        return $query->where('material_type', 'file');
    }

    public function scopeVideos($query)
    {
        return $query->where('material_type', 'video')
            ->orWhereNotNull('video_url');
    }

    public function scopeQuizzes($query)
    {
        return $query->where('material_type', 'quiz');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeHcwTraining($query)
    {
        return $query->where('category', 'hcw-training');
    }

    public function scopeWeekGuide($query, ?int $week = null)
    {
        $q = $query->whereNotNull('week_number');
        return $week ? $q->where('week_number', $week) : $q;
    }

    /**
     * Check if material has a playable video (MP4 file or streaming link).
     */
    public function isPlayableVideo(): bool
    {
        if ($this->material_type === 'video' || !empty($this->video_url)) {
            return true;
        }

        return $this->isDirectVideoFile();
    }

    /**
     * Check if the uploaded file is a direct video file (MP4, WEBM, MOV, AVI).
     */
    public function isDirectVideoFile(): bool
    {
        if (!$this->file) {
            return false;
        }

        $ext = strtolower(pathinfo($this->file, PATHINFO_EXTENSION));
        return in_array($ext, ['mp4', 'webm', 'mov', 'avi', 'm4v'], true);
    }

    /**
     * Get the YouTube (nocookie) / Vimeo embed URL from watch / share URLs.
     */
    public function getEmbedUrlAttribute(): ?string
    {
        // Prefer the privacy-enhanced YouTube embed when we have an ID.
        if ($this->youtube_embed_url) {
            return $this->youtube_embed_url;
        }

        if (!$this->video_url) {
            return null;
        }

        $url = trim($this->video_url);

        // YouTube watch URL or shorts or youtu.be
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]+)/', $url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/' . $m[1] . '?rel=0&modestbranding=1';
        }

        // Vimeo URL
        if (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|)(\d+)/', $url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }

        return $url;
    }

    public function getImageUrlAttribute(): ?string
    {
        $imageUrl = $this->resolvePublicAssetUrl($this->image);

        if ($imageUrl) {
            return $imageUrl;
        }

        if ($this->isPreviewableFile()) {
            return $this->resolvePublicAssetUrl($this->file);
        }

        return null;
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->resolvePublicAssetUrl($this->file);
    }

    public function isPreviewableFile(): bool
    {
        if (!$this->file) {
            return false;
        }

        return in_array(strtolower(pathinfo($this->file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }

    private function resolvePublicAssetUrl(?string $path): ?string
    {
        $normalizedPath = $this->normalizeStoredPath($path);

        if (!$normalizedPath) {
            return null;
        }

        $publicStoragePath = public_path('storage/' . $normalizedPath);
        if (File::exists($publicStoragePath)) {
            return asset('storage/' . $normalizedPath);
        }

        $storageAppPath = storage_path('app/public/' . $normalizedPath);
        if (File::exists($storageAppPath)) {
            File::ensureDirectoryExists(dirname($publicStoragePath));
            File::copy($storageAppPath, $publicStoragePath);

            return asset('storage/' . $normalizedPath);
        }

        $directPublicPath = public_path($normalizedPath);
        if (File::exists($directPublicPath)) {
            return asset($normalizedPath);
        }

        return asset('storage/' . $normalizedPath);
    }

    private function normalizeStoredPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');

        foreach (['storage/app/public/', 'public/storage/', 'storage/'] as $prefix) {
            if (str_starts_with($normalizedPath, $prefix)) {
                $normalizedPath = substr($normalizedPath, strlen($prefix));
                break;
            }
        }

        return $normalizedPath;
    }
}
