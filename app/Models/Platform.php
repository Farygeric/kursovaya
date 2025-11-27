<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    protected $table = 'platforms';
    protected $fillable = ['name'];

    public function games()
    {
        return $this->belongsToMany(Game::class, 'game_platform');
    }
}