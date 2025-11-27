<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponsibilityItem extends Model
{
    protected $fillable = ['text'];

    public function vacancies() {
        return $this->belongsToMany(Vacancy::class, 'vacancy_responsibility');
    }

    protected $casts = ['text' => 'string'];
}