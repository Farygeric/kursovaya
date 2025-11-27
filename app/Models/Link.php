<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $fillable = [
        'url',
        'label'
    ];

    public function games()
    {
        return $this->belongsToMany(Game::class, 'game_link');
    }
}