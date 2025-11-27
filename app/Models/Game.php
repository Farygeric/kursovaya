<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'name',
        'about_game',
        'trailer_link'
    ];

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'game_genre');
    }

    public function platforms()
    {
        return $this->belongsToMany(Platform::class, 'game_platform');
    }

    public function links()
    {
        return $this->belongsToMany(Link::class, 'game_link');
    }

    public function images()
    {
        return $this->hasMany(GameImage::class);
    }

    public function mainImage()
    {
        return $this->hasOne(GameImage::class)->where('is_main', true);
    }

    public function screenshots()
    {
        return $this->hasMany(GameImage::class)
            ->where('is_main', false)
            ->orderBy('sort_order');
    }
}