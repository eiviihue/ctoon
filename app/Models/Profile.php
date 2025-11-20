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
        $disk = config('filesystems.default', env('FILESYSTEM_DISK', 'public'));
        try {
            $filesystem = Storage::disk($disk);
            if (method_exists($filesystem, 'url')) {
                return $filesystem->url($this->avatar_path);
            }
        } catch (\Exception $e) {
            // fall back gracefully
        }

        return asset('storage/' . ltrim($this->avatar_path, '/'));
    }
}

