<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Comic extends Model
{
    protected $fillable = ['title', 'slug', 'author', 'description', 'cover_path', 'genre_id'];

    protected static function booted()
    {
        static::creating(function ($comic) {
            if (empty($comic->slug)) {
                $comic->slug = Str::slug($comic->title) . '-' . Str::random(6);
            }
        });
    }

    public function genre()
    {
        return $this->belongsTo(Genre::class);
    }
    public function chapters()
    {
        return $this->hasMany(Chapter::class)->orderBy('number');
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function averageRating()
    {
        return round($this->ratings()->avg('rating'), 2) ?: 0;
    }

    /**
     * Accessor to get the public URL for the comic cover.
     */
    public function getCoverUrlAttribute()
    {
        if (empty($this->cover_path)) {
            return null;
        }

        $disk = config('filesystems.default', env('FILESYSTEM_DISK', 'local'));
        // Prefer filesystem helper if available at runtime
        try {
            $filesystem = Storage::disk($disk);
            if (method_exists($filesystem, 'url')) {
                return $filesystem->url($this->cover_path);
            }
        } catch (\Exception $e) {
            // ignore and fallthrough to build URL
        }

        // If a full blob endpoint with SAS is provided in env, use it (format: https://.../container?sv=...)
        $blobEndpoint = env('AZURE_STORAGE_BLOB_ENDPOINT');
        if (!empty($blobEndpoint)) {
            // split query if present
            $parts = explode('?', $blobEndpoint, 2);
            $base = rtrim($parts[0], '/');
            $query = isset($parts[1]) ? $parts[1] : null;
            $url = $base . '/' . ltrim($this->cover_path, '/');
            if ($query) $url .= '?' . $query;
            return $url;
        }

        // Build URL from disk config if present (e.g., AZURE blob url without SAS)
        $diskConfig = config('filesystems.disks.' . $disk, []);
        if (!empty($diskConfig['url'])) {
            return rtrim($diskConfig['url'], '/') . '/' . ltrim($this->cover_path, '/');
        }

        // Last fallback - local public storage
        return asset('storage/' . $this->cover_path);
    }
}

