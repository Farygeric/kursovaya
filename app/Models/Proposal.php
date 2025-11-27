<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    protected $fillable = [
        'name', 'email', 'subject', 'message',
        'file_src', 'status', 'privacy_agreement'
    ];

    protected $casts = [
        'privacy_agreement' => 'boolean',
    ];
}