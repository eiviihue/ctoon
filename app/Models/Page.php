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
        // If an Azure storage account is configured and an 'azure' disk exists, prefer it
        $preferredDisk = config('filesystems.default', env('FILESYSTEM_DISK', 'local'));
        if (!empty(env('AZURE_STORAGE_ACCOUNT')) && array_key_exists('azure', config('filesystems.disks', []))) {
            $disk = 'azure';
        } else {
            $disk = $preferredDisk;
        }

        try {
            $filesystem = Storage::disk($disk);
            if (method_exists($filesystem, 'url')) {
                return $filesystem->url($this->image_path);
            }
        } catch (\Exception $e) {
            // ignore and build fallback URL below
        }

        // Build a blob URL using standard environment variables if available
        $storageAccount = env('AZURE_STORAGE_ACCOUNT') ?: env('AZURE_STORAGE_NAME');
        $container = env('AZURE_STORAGE_CONTAINER', env('AZURE_STORAGE_CONTAINER_NAME', 'images'));
        if (!empty($storageAccount) && !empty($container)) {
            return "https://{$storageAccount}.blob.core.windows.net/{$container}/" . ltrim($this->image_path, '/');
        }

        // Try disk config url
        $diskConfig = config('filesystems.disks.' . $disk, []);
        if (!empty($diskConfig['url'])) {
            return rtrim($diskConfig['url'], '/') . '/' . ltrim($this->image_path, '/');
        }

        // Final fallback: local storage asset
        return asset('storage/' . ltrim($this->image_path, '/'));
    }
}

