<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Profile extends Model
{
    protected $fillable = ['user_id', 'avatar_path', 'bio'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAvatarUrlAttribute()
    {
        if (empty($this->avatar_path)) return null;
        $disk = config('filesystems.default', env('FILESYSTEM_DISK', 'local'));
        try {
            $filesystem = Storage::disk($disk);
            if (method_exists($filesystem, 'url')) {
                return $filesystem->url($this->avatar_path);
            }
        } catch (\Exception $e) {
            // ignore
        }

        // Build Azure public URL if storage info is present
        $storageAccount = env('AZURE_STORAGE_NAME') ?: env('AZURE_STORAGE_ACCOUNT');
        $container = env('AZURE_STORAGE_CONTAINER') ?: env('AZURE_STORAGE_CONTAINER_NAME');
        if (!empty($storageAccount) && !empty($container)) {
            $path = ltrim($this->avatar_path, '/');
            return "https://{$storageAccount}.blob.core.windows.net/{$container}/" . $path;
        }

        $diskConfig = config('filesystems.disks.' . $disk, []);
        if (!empty($diskConfig['url'])) {
            return rtrim($diskConfig['url'], '/') . '/' . ltrim($this->avatar_path, '/');
        }
        return asset('storage/' . ltrim($this->avatar_path, '/'));
    }
}

