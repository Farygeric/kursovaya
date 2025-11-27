<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'vacancy_id', 'name', 'email', 'phone', 'message',
        'resume', 'privacy_agreement'
    ];

    protected $casts = [
        'privacy_agreement' => 'boolean',
    ];

    public function vacancy() { return $this->belongsTo(Vacancy::class); }
}