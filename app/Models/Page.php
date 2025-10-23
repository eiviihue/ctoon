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
        // Use public Azure URL without SAS token
        $storageAccount = env('AZURE_STORAGE_NAME');
        $container = env('AZURE_STORAGE_CONTAINER', 'images');
        if (!empty($storageAccount)) {
            return "https://{$storageAccount}.blob.core.windows.net/{$container}/" . ltrim($this->image_path, '/');
        }

        // Fallback to disk url or asset
        $disk = config('filesystems.default', env('FILESYSTEM_DISK', 'local'));
        try {
            $filesystem = Storage::disk($disk);
            if (method_exists($filesystem, 'url')) {
                return $filesystem->url($this->image_path);
            }
        } catch (\Exception $e) {
            // ignore
        }
        $diskConfig = config('filesystems.disks.' . $disk, []);
        if (!empty($diskConfig['url'])) {
            return rtrim($diskConfig['url'], '/') . '/' . ltrim($this->image_path, '/');
        }
        return asset('storage/' . $this->image_path);
    }
}

