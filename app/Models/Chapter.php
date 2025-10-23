<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = ['comic_id', 'number', 'title', 'published_at'];

    public function comic()
    {
        return $this->belongsTo(Comic::class);
    }
    public function pages()
    {
        return $this->hasMany(Page::class)->orderBy('page_number');
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

