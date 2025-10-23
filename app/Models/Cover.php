<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cover extends Model
{
    protected $fillable = ['comic_id', 'path', 'filename', 'size', 'disk', 'is_primary'];

    public function comic()
    {
        return $this->belongsTo(Comic::class);
    }
}
