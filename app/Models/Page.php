<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Page extends Model
{
    protected $fillable = ['chapter_id', 'image_path', 'page_number'];
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function getImageUrlAttribute()
    {
        if (empty($this->image_path)) return null;
        $disk = config('filesystems.default', env('FILESYSTEM_DISK', 'public'));
        try {
            $filesystem = Storage::disk($disk);
            if (method_exists($filesystem, 'url')) {
                return $filesystem->url($this->image_path);
            }
        } catch (\Exception $e) {
            // fall back
        }

        return asset('storage/' . ltrim($this->image_path, '/'));
    }
}

