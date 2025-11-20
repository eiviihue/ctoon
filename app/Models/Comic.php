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
        $disk = config('filesystems.default', env('FILESYSTEM_DISK', 'public'));
        try {
            $filesystem = Storage::disk($disk);
            if (method_exists($filesystem, 'url')) {
                return $filesystem->url($this->cover_path);
            }
        } catch (\Exception $e) {
            // fall back
        }

        return asset('storage/' . ltrim($this->cover_path, '/'));
    }
}

