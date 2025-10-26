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
        // First try configured filesystem disk URL (works if azure disk driver is configured)
        $preferredDisk = config('filesystems.default', env('FILESYSTEM_DISK', 'local'));
        try {
            $filesystem = Storage::disk($preferredDisk);
            if (method_exists($filesystem, 'url')) {
                return $filesystem->url($this->cover_path);
            }
        } catch (\Exception $e) {
            // ignore and fall back to building Azure public URL
        }

        // If Azure storage account name + container are available, build public URL (assumes anonymous blobs)
        $storageAccount = env('AZURE_STORAGE_NAME') ?: env('AZURE_STORAGE_ACCOUNT');
        $container = env('AZURE_STORAGE_CONTAINER') ?: env('AZURE_STORAGE_CONTAINER_NAME');
        if (!empty($storageAccount) && !empty($container)) {
            $path = ltrim($this->cover_path, '/');
            $baseUrl = "https://{$storageAccount}.blob.core.windows.net/{$container}/" . $path;
            return $baseUrl;
        }

        // Try disk config url as a fallback
        $diskConfig = config('filesystems.disks.' . $preferredDisk, []);
        if (!empty($diskConfig['url'])) {
            return rtrim($diskConfig['url'], '/') . '/' . ltrim($this->cover_path, '/');
        }

        // Final fallback - local storage
        return asset('storage/' . ltrim($this->cover_path, '/'));
    }
}

