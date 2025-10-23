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
        $preferredDisk = config('filesystems.default', env('FILESYSTEM_DISK', 'local'));
        if (!empty(env('AZURE_STORAGE_ACCOUNT')) && array_key_exists('azure', config('filesystems.disks', []))) {
            $disk = 'azure';
        } else {
            $disk = $preferredDisk;
        }

        // Preferred: use filesystem disk helper (this will work for Azure disk driver)
        try {
            $filesystem = Storage::disk($disk);
            if (method_exists($filesystem, 'url')) {
                return $filesystem->url($this->cover_path);
            }
        } catch (\Exception $e) {
            // ignore and continue to fallbacks
        }

        // If a full blob endpoint with SAS is provided in env, use it (format: https://.../container?sv=...)
        $blobEndpoint = env('AZURE_STORAGE_BLOB_ENDPOINT');
        if (!empty($blobEndpoint)) {
            $parts = explode('?', $blobEndpoint, 2);
            $base = rtrim($parts[0], '/');
            $query = isset($parts[1]) ? $parts[1] : null;
            $url = $base . '/' . ltrim($this->cover_path, '/');
            if ($query) $url .= '?' . $query;
            return $url;
        }

        // Build URL from standard AZURE env variables if available
        $storageAccount = env('AZURE_STORAGE_ACCOUNT') ?: env('AZURE_STORAGE_NAME');
        $container = env('AZURE_STORAGE_CONTAINER', env('AZURE_STORAGE_CONTAINER_NAME', null));
        if (!empty($storageAccount) && !empty($container)) {
            return "https://{$storageAccount}.blob.core.windows.net/{$container}/" . ltrim($this->cover_path, '/');
        }

        // Try disk config url
        $diskConfig = config('filesystems.disks.' . $disk, []);
        if (!empty($diskConfig['url'])) {
            return rtrim($diskConfig['url'], '/') . '/' . ltrim($this->cover_path, '/');
        }

        // Final fallback - local storage
        return asset('storage/' . ltrim($this->cover_path, '/'));
    }
}

