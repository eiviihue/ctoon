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
        $diskConfig = config('filesystems.disks.' . $disk, []);
        if (!empty($diskConfig['url'])) {
            return rtrim($diskConfig['url'], '/') . '/' . ltrim($this->avatar_path, '/');
        }
        return asset('storage/' . $this->avatar_path);
    }
}

