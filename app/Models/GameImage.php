<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameImage extends Model
{
    protected $fillable = [
        'game_id',
        'path',
        'is_main',
        'sort_order'
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}